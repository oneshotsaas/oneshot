# Billing Module

Credit-based subscription billing for OneShot. Provider-agnostic, immutable ledger, atomic operations.

---

## Quick Start

```php
$billing = new \OneShot\Billing\Services\BillingService();

// Check balance
$balance = $billing->getBalance($userId);

// Charge for an action (before execution)
$ok = $billing->charge($userId, 'image.generate.dall-e-3', ['size' => '1024x1024']);

// Charge after execution (mandatory, idempotency key required)
$billing->chargeForce($userId, 'llm.gpt-4', [
    'input_tokens'  => 800,
    'output_tokens' => 1200,
    'cached_tokens' => 300,
], $idempotencyKey);

// Estimate before acting
$est = $billing->estimate($userId, 'video.generate.runway', ['seconds' => 30, 'audio' => true]);
// ['cost' => 15.0, 'can_afford' => true, 'balance' => 42.5, 'would_use_overdraft' => false]

// Check feature access
if ($billing->hasFeature($userId, 'api_access')) { ... }
```

---

## Architecture

### Two payment slots

Configured via Admin → Billing → Setup:

| Setting | Purpose | Default |
|---|---|---|
| `billing.subscription_provider` | Recurring subscriptions | `stripe` |
| `billing.payment_provider` | One-time purchases (packages, top-ups) | `stripe` |

Both support comma-separated multi-provider values (`stripe,coinbase`). If multiple — user sees a provider selection page before checkout.

### Service factory

```php
service('subscriptionPayment', 'stripe')  // → Providers\Stripe\Stripe
service('oneTimePayment', 'stripe')       // → Providers\Stripe\Stripe
service('oneTimePayment', 'coinbase')     // → Providers\Coinbase\Coinbase
```

Unknown provider throws `\RuntimeException`. Both methods are in `app/Config/Services.php`.

### Provider refs (`billing_provider_refs`)

Stores external IDs for all provider entities:

| `entity_type` | `entity_id` | Meaning |
|---|---|---|
| `customer` | `user_id` | Stripe Customer ID |
| `subscription` | local sub ID | Stripe Subscription ID |
| `plan` | local plan ID | Stripe Product ID |
| `plan_price` | local price ID | Stripe Price ID |
| `promotion` | local promo ID | Stripe Coupon ID |
| `invoice` | local invoice ID | Stripe Invoice ID |

The `meta` column stores a JSON snapshot for diff detection — used in plan price sync:
```json
{"amount": 2900, "currency": "usd", "interval": "month"}
```

Access via `BillingService`:
```php
$billing->getProviderRef('customer', $userId, 'stripe');            // → object|null
$billing->setProviderRef('customer', $userId, 'stripe', 'cus_xxx'); // set ref
$billing->setProviderRef('plan_price', $priceId, 'stripe', 'price_xxx', $snapshot); // with meta
```

---

## Action Naming Convention

Actions are stored in `billing_costs.action` as dot-separated strings encoding operation + provider/model:

```
image.generate.dall-e-3
image.generate.gemini-flash
video.generate.runway
llm.gpt-4
llm.claude-sonnet
audio.tts.elevenlabs
```

Different providers for the same operation = separate rows in `billing_costs`.

---

## Cost Configuration (`billing_costs`)

Each action has a `unit_type` and optional `meta` JSON:

### unit (images, documents, API calls)
```json
{
  "param": "size",
  "variants": {
    "512x512":   {"type": "multiply", "value": 1},
    "1024x1024": {"type": "multiply", "value": 2},
    "2048x2048": {"type": "fixed",    "value": 15}
  }
}
```
- `multiply` → `cost_per_unit × qty × value`
- `fixed` → always return `value` credits regardless of `cost_per_unit`

### second (video, audio)
```json
{
  "param": "seconds",
  "variants": {
    "audio": {"type": "multiply", "value": 1.5},
    "4k":    {"type": "multiply", "value": 2.0}
  }
}
```
Multipliers stack — all matching boolean params are applied together.

### token (LLM)
```json
{
  "batch": 1000,
  "output_multiplier": 3,
  "cached_discount": 0.5
}
```
Formula: `ceil((input + output × output_multiplier − cached × cached_discount) / batch) × cost_per_unit`

---

## Overdraft Modes

Set via Admin → Billing → Setup → `billing.overdraft_mode`:

| Value | Behavior |
|---|---|
| `deny` (default) | Block if balance insufficient |
| `once` | Allow going negative once per user (`overdraft_used` flag) |
| `limit` | Allow down to `billing.overdraft_limit` floor (e.g. `-50`) |

`charge()` respects overdraft_mode. `chargeForce()` always deducts (use after completed action).

---

## Subscription Flow

### First subscription
1. User clicks Subscribe → `Subscribe::store()` → rate limited (5/min per user)
2. Fallback check: if no Stripe key AND `ENVIRONMENT !== 'production'` → local subscribe (dev/demo)
3. Get/create Stripe Customer via `getOrCreateCustomer()` → stored in `billing_provider_refs(entity_type=customer)`
4. Look up Stripe Price ID from `billing_provider_refs(entity_type=plan_price)` — if missing → error "payment unavailable"
5. If promo code → `getOrCreateCoupon()` → lookup or create Stripe Coupon → stored in `billing_provider_refs(entity_type=promotion)`
6. `checkout()` with `mode=subscription`, `metadata={user_id, plan_id, interval, promo_code, type=subscription}`
7. On `checkout.session.completed` webhook → `billing->subscribe()` + store refs + grant credits + notify

### Upgrade (existing active subscription)
1. User selects new plan → `Subscribe::store()`
2. Detects active local subscription with `provider` set and a `billing_provider_refs(entity_type=subscription)` ref
3. Calls `provider->updateSubscription($stripeSubId, $newPriceId, $collectionMethod)`
4. `collection_method = billing.upgrade_collection_method` setting (default: `send_invoice`)
   - `send_invoice` → Stripe creates an invoice → redirect to `hosted_invoice_url` for manual payment
   - `charge_automatically` → Stripe charges card on file immediately → flash message
5. On `invoice.paid` webhook with `billing_reason=subscription_update` → `billing->upgradeCredits()` + update local plan

### upgradeCredits logic
- Only **adds** proportional new plan credits for remaining days: `round(credits × remainingDays / totalDays)`
- Never deducts existing balance
- Idempotent: checks `last_upgrade_invoice_id` — if invoice already processed, skips

### Cancellation (cancel at period end)
1. `Subscribe::cancel()` → calls `provider->cancelAtPeriodEnd($stripeSubId)` on Stripe
2. Sets `cancel_at_period_end = 1` locally — access remains until `current_period_end`
3. When period ends → Stripe fires `customer.subscription.deleted` → `onSubscriptionDeleted()` marks `status=canceled`
4. No cron needed — Stripe handles the lifecycle

### Trial
- `trial_days > 0` on plan → `subscription_data.trial_period_days` passed to Stripe Checkout
- Trial credits: `trial_credits` column on plan (explicit amount); if NULL → proportional: `trial_days / 30 × credits_included`
- Trial requires card (Stripe shows $0 checkout but captures payment method)

---

## Package Purchase Flow

1. `Packages::buy()` → rate limit optional
2. Fallback: no key + non-production → local grant
3. Single provider → `processPackageCheckout()` directly
4. Multiple providers → redirect to `selectProvider()` page → POST to `buyWithProvider()`
5. Effective price = `package.price` minus promo discount (calculated locally via `calcPromoDiscount()`)
6. Always goes through Stripe even at $0 — card must be on file
7. `checkout()` with `mode=payment`, `payment_intent_data.setup_future_usage=off_session`
8. On `checkout.session.completed` → `purchasePackage()` + grant credits + notify

---

## Promo Codes

### One-time purchases (packages)
Discount calculated locally via `BillingService::calcPromoDiscount()`. The discounted `unit_amount` is passed directly to Stripe Checkout.

### Subscriptions
Must use Stripe Coupons — changing `unit_amount` would lock the discounted price forever.

Flow in `Subscribe::getOrCreateCoupon()`:
1. Validate promo code via `Promotion::findByCode()`
2. Check `billing_provider_refs(entity_type=promotion, provider=stripe)` for existing Coupon ID
3. If none → `provider->createCoupon(name, discount_type, discount_value, duration)`
4. Store ref, pass `discounts[0][coupon]` to Checkout Session

`subscription_discount_duration` on the promotion:
- `once` → discount on first payment only, full price from next renewal
- `forever` → discount on every renewal

**100% discount** — still goes through Stripe Checkout (not bypassed). Stripe shows $0 but requires card. Card is saved for future renewals at full price.

---

## Credits Grant Modes (`credits_grant` on `billing_plan_prices`)

| Value | Behavior |
|---|---|
| `full` | All credits granted immediately on subscribe/renew. Annual plan → all 12 months at once. |
| `monthly` | `credits_included` per month. TaskRunner delivers subsequent months. |

`full` is the default and requires no background processing.

---

## Billing Portal

```php
// Subscribe::portal()
$session = service('subscriptionPayment', $sub->provider)->portal($customerId, $returnUrl);
redirect()->to($session['url']);
```

User manages payment methods, invoices, cancellations directly in Stripe-hosted portal.

---

## Refunds (Admin)

Admin → Transactions → Refund button (visible on credit transactions with `ref_type=invoice`).

Form fields:
- `refund_amount` — dollars, max = invoice amount
- `credits_action`:
  - `proportional` (default) — deduct `refund_amount / invoice_amount × credits_granted`
  - `all` — deduct all credits granted by this invoice
  - `none` — refund money, keep credits

Flow in `Transactions::refund()`:
1. Find `charge_id` from `invoice.data` JSON
2. Detect provider from `billing_subscriptions.provider` via subscription ref
3. `service('oneTimePayment', $provider)->refund($chargeId, $amountCents)`
4. Mark invoice `status=refunded`
5. Deduct credits via `billing->deduct()` with `force=true` (can go negative)
6. Fire `event('billing.refund_issued')` + `notify()`

### Webhook-initiated refunds (`charge.refunded`)
For refunds done directly in Stripe Dashboard (bypassing admin UI):
- `StripeHandler::onChargeRefunded()` finds invoice by `charge_id` in `data` JSON
- Applies `proportional` strategy automatically
- Skips if invoice already `status=refunded` (deduplication)

---

## Provider Sync (Admin → Billing → Setup)

### Why sync is needed
Stripe uses its own Price IDs (immutable, once created cannot be changed). Local `billing_plan_prices` must be mirrored to Stripe before checkout can work.

### Sync flow (`Install::syncProvider()`)
For each active plan:
1. If no `billing_provider_refs(entity_type=plan)` → `createOrUpdateProduct()` → store ref
2. For each `billing_plan_prices` row:
   - No ref → `createPrice()` → store ref + snapshot `{amount, currency, interval}` in `meta`
   - Ref exists, snapshot matches → skip
   - Ref exists, snapshot differs → `archivePrice(oldId)` + `createPrice()` → update ref + snapshot

**Why archive instead of update:** Stripe Prices are immutable. You cannot change `unit_amount`, `currency`, or `interval` on an existing price. The old price must be archived (deactivated) and a new price created.

### Sync status per price
- `ok` — ref exists, snapshot matches current values
- `outdated` — ref exists, snapshot differs (run Sync All to fix)
- `missing` — no ref (run Sync All to create)

Errors are logged via `l()` and returned in response; sync continues even if individual prices fail.

### When sync is triggered
- **Manual only** — via "Sync All" button in Setup (AJAX POST)
- CRUD operations on Plans/PlanPrices send an admin notification (`billing.provider_sync_required`) but do NOT auto-sync

---

## Webhooks

URL pattern: `/api/v1/billing/webhook/{provider}/{token}`

- `{provider}` — lowercase: `stripe`, `coinbase`
- `{token}` — per-provider URL secret: `billing.webhook_secret_{provider}` (auto-generated on first settings save)

Token mismatch returns 404 (not 403 — don't confirm endpoint exists).

### Stripe events handled

| Event | Handler | Action |
|---|---|---|
| `checkout.session.completed` | `onCheckoutCompleted` | subscription → `subscribe()` + refs; payment → `purchasePackage()` + credits |
| `invoice.paid` | `onInvoicePaid` | renewal → `renewCredits()`; upgrade (`billing_reason=subscription_update`) → `upgradeCredits()` |
| `customer.subscription.updated` | `onSubscriptionUpdated` | sync status, period dates, `cancel_at_period_end` |
| `customer.subscription.deleted` | `onSubscriptionDeleted` | mark `status=canceled` locally |
| `invoice.payment_failed` | `onPaymentFailed` | mark `past_due`, increment `retry_count`, notify user |
| `charge.refunded` | `onChargeRefunded` | mark invoice refunded, deduct proportional credits, notify |

All events are deduplicated via `billing_webhook_events` table.

### Settings translations

All billing settings labels and hints are in `oneshot/Billing/Language/en/billing.php`.
Key format: `{param}` for label, `{param}_hint` for the hint shown below the field.

---

## Settings Reference

| Key | Type | Description |
|---|---|---|
| `billing.overdraft_mode` | select | `deny` / `once` / `limit` |
| `billing.overdraft_limit` | text | Floor for `limit` mode (e.g. `-50`) |
| `billing.subscription_provider` | text | Provider(s) for subscriptions (comma-separated) |
| `billing.payment_provider` | text | Provider(s) for one-time purchases (comma-separated) |
| `billing.upgrade_collection_method` | select | `send_invoice` / `charge_automatically` |
| `billing.stripe_secret_key` | password | Stripe secret key (`sk_live_...`) |
| `billing.stripe_publishable_key` | text | Stripe publishable key (`pk_live_...`) |
| `billing.stripe_webhook_secret` | password | Stripe signing secret (`whsec_...`) |
| `billing.webhook_secret_stripe` | password | URL token for Stripe webhook URL |
| `billing.coinbase_api_key` | password | Coinbase Commerce API key |
| `billing.coinbase_webhook_secret` | password | Coinbase shared secret |
| `billing.webhook_secret_coinbase` | password | URL token for Coinbase webhook URL |

---

## Adding a New Payment Provider

1. Implement `OneShot\Core\Contracts\Payment` interface in `providers/{Name}/{Name}.php`
2. Register in `app/Config/Services::resolvePaymentProvider()`:
   ```php
   'newprovider' => new \Providers\NewProvider\NewProvider(option('billing.newprovider_api_key', '')),
   ```
3. Create webhook handler `oneshot/Billing/Webhooks/{Name}Handler.php` with `verifySignature()`, `getEventId()`, `getEventType()`, `dispatch()`
4. Add settings `billing.newprovider_api_key` and `billing.webhook_secret_newprovider`
5. Register webhook URL in provider dashboard: `/api/v1/billing/webhook/newprovider/{token}`

---

## Database Protection

For maximum ledger integrity, revoke mutation rights from the app DB user:

```sql
REVOKE DELETE, UPDATE ON billing_transactions FROM 'app_user'@'%';
```

`billing_transactions` is append-only. The `Transaction` model also throws `\LogicException` on any `delete()` or `update()` call at the PHP level.

---

## Maintenance

### Balance drift check
```php
$billing = new BillingService();
$drifted = $billing->auditBalance();    // all users
$drifted = $billing->auditBalance($userId); // single user
```
Drift is logged to `billing_drift`. To repair: `$billing->rebuildBalance($userId)`.

### Stuck webhooks
```sql
UPDATE billing_webhook_events
SET status = 'failed'
WHERE status = 'processing'
  AND processing_started_at < NOW() - INTERVAL 10 MINUTE;
```
Set as a cron (every 15 minutes).

### Webhook event cleanup
```sql
DELETE FROM billing_webhook_events
WHERE status = 'processed'
  AND processed_at < NOW() - INTERVAL 90 DAY;
```

---

## Routes Reference

```
GET  billing                                    billing.index
GET  billing/plans                              billing.plans
GET  billing/subscribe/{hash}                   billing.subscribe
POST billing/subscribe/{hash}
GET  billing/subscribe-success                  billing.subscribe.success
GET  billing/subscribe-cancel                   billing.subscribe.cancelled
POST billing/cancel                             billing.cancel
POST billing/portal                             billing.portal
GET  billing/packages                           billing.packages
POST billing/packages/buy/{hash}                billing.packages.buy
GET  billing/packages/select-provider/{hash}    billing.packages.select_provider
POST billing/packages/buy/{hash}/{provider}     billing.packages.buy_with_provider
GET  billing/usage                              billing.usage
GET  billing/invoices                           billing.invoices
GET  billing/invoices/{hash}                    billing.invoice

GET  admin/billing/plans                        admin.billing.plans
GET  admin/billing/plans/create                 admin.billing.plans.create
GET  admin/billing/plans/{hash}                 admin.billing.plan.edit
GET  admin/billing/plans/{hash}/prices          admin.billing.plan.prices
GET  admin/billing/promotions                   admin.billing.promotions
GET  admin/billing/packages                     admin.billing.packages
GET  admin/billing/costs                        admin.billing.costs
GET  admin/billing/subscriptions                admin.billing.subscriptions
GET  admin/billing/transactions                 admin.billing.transactions
GET  admin/billing/transactions/{hash}/refund   admin.billing.transaction.refund
POST admin/billing/transactions/{hash}/refund
GET  admin/billing/invoices                     admin.billing.invoices
GET  admin/billing/topup/{hash}                 admin.billing.topup
GET  admin/billing/install                      admin.billing.install
POST admin/billing/install/settings             admin.billing.install.settings
POST admin/billing/install/sync/{provider}      admin.billing.install.sync
GET  admin/billing/install/status/{provider}    admin.billing.install.status
POST admin/billing/install/token                admin.billing.install.token
POST admin/billing/install/demo                 admin.billing.install.demo

POST api/v1/billing/webhook/{provider}/{token}  billing.webhook
```
