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

Set via Admin → Settings → `billing.overdraft_mode`:

| Value | Behavior |
|---|---|
| `deny` (default) | Block if balance insufficient |
| `once` | Allow going negative once per user (`overdraft_used` flag) |
| `limit` | Allow down to `billing.overdraft_limit` floor (e.g. `-50`) |

`charge()` respects overdraft_mode. `chargeForce()` always deducts (use after completed action).

---

## Webhooks

URL pattern: `/api/v1/billing/webhook/{provider}/{token}`

- `{provider}` — lowercase: `stripe`, `coinbase`, etc.
- `{token}` — per-provider URL secret from settings: `billing.webhook_secret_{provider}`

Token mismatch returns 404 (not 403 — don't confirm endpoint exists).

### Settings translations

All billing settings labels and hints are defined in `oneshot/Billing/Language/en/billing.php`.
The Settings UI resolves them automatically:
- Label: `__('billing.{param}', $dbLabel)` → e.g. key `overdraft_mode` in `billing.php`
- Hint: `__('billing.{param}_hint', '')` → shown below the field if the key exists

When adding new billing settings, add matching `{param}` and `{param}_hint` entries to `billing.php`.

### Stripe settings
| Key | Description |
|---|---|
| `billing.stripe_secret_key` | Secret key (`sk_live_...`) |
| `billing.stripe_publishable_key` | Publishable key (`pk_live_...`) |
| `billing.stripe_webhook_secret` | Signing secret from Stripe dashboard (`whsec_...`) |
| `billing.webhook_secret_stripe` | URL token — put this in the Stripe webhook URL |

**Stripe webhook URL:** `https://yoursite.com/api/v1/billing/webhook/stripe/{billing.webhook_secret_stripe}`

### Coinbase settings
| Key | Description |
|---|---|
| `billing.coinbase_api_key` | Commerce API key |
| `billing.coinbase_webhook_secret` | Shared secret for payload verification |
| `billing.webhook_secret_coinbase` | URL token |

---

## Adding a New Payment Provider

1. **Settings** — add via Admin → Settings or migration:
   - `billing.{provider}_api_key`
   - `billing.webhook_secret_{provider}` — auto-generate 32-char hex
   - Any other credentials specific to the provider

2. **Handler** — create `oneshot/Billing/Webhooks/{Provider}Handler.php`:
   ```php
   class PaddleHandler {
       public function verifySignature(): void { /* verify payload signature */ }
       public function getEventId(): string { /* return event ID */ }
       public function getEventType(): string { /* return event type */ }
       public function dispatch(BillingService $billing): void { /* handle events */ }
   }
   ```
   Handler class name must match: `{ucfirst($provider)}Handler`

3. **Provider refs** — store provider IDs via:
   ```php
   $billing->setProviderRef('subscription', $subId, 'paddle', $paddleSubscriptionId);
   $billing->setProviderRef('customer', $userId, 'paddle', $paddleCustomerId);
   ```

4. **Webhook URL** — register in provider dashboard:
   `https://yoursite.com/api/v1/billing/webhook/paddle/{billing.webhook_secret_paddle}`

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
$drifted = $billing->auditBalance(); // returns users with balance mismatch
// or check single user:
$drifted = $billing->auditBalance($userId);
```
Drift is logged to `billing_drift`. To repair: `$billing->rebuildBalance($userId)`.

### Stuck webhooks
Webhooks stuck in `processing` status for > 10 minutes should be reset to `failed` so the next retry can take ownership:
```sql
UPDATE billing_webhook_events
SET status = 'failed'
WHERE status = 'processing'
  AND processing_started_at < NOW() - INTERVAL 10 MINUTE;
```
Set this as a cron job (every 15 minutes).

### Webhook event cleanup
Delete old processed events (providers may retry for weeks):
```sql
DELETE FROM billing_webhook_events
WHERE status = 'processed'
  AND processed_at < NOW() - INTERVAL 90 DAY;
```

---

## Routes Reference

```
GET  /app/billing                           billing.index
GET  /app/billing/plans                     billing.plans
GET  /app/billing/subscribe/{hash}          billing.subscribe
POST /app/billing/subscribe/{hash}
GET  /app/billing/subscribe-success         billing.subscribe.success
POST /app/billing/cancel                    billing.cancel
GET  /app/billing/packages                  billing.packages
POST /app/billing/packages/buy/{hash}       billing.packages.buy
GET  /app/billing/usage                     billing.usage
GET  /app/billing/invoices                  billing.invoices
GET  /app/billing/invoices/{hash}           billing.invoice

GET  /admin/billing/plans                   admin.billing.plans
GET  /admin/billing/plans/create            admin.billing.plans.create
GET  /admin/billing/plans/{hash}            admin.billing.plan.edit
GET  /admin/billing/plans/{hash}/prices     admin.billing.plan.prices
GET  /admin/billing/promotions              admin.billing.promotions
GET  /admin/billing/packages                admin.billing.packages
GET  /admin/billing/costs                   admin.billing.costs
GET  /admin/billing/subscriptions           admin.billing.subscriptions
GET  /admin/billing/transactions            admin.billing.transactions
GET  /admin/billing/invoices                admin.billing.invoices
GET  /admin/billing/topup/{hash}            admin.billing.topup

POST /api/v1/billing/webhook/{provider}/{token}   billing.webhook
```
