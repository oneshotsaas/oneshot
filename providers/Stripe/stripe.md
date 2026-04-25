# Stripe Provider

`providers/Stripe/Stripe.php` — implementation of `OneShot\Core\Contracts\Payment` via raw cURL (no Stripe SDK, no Composer).

---

## Initialization

```php
$stripe = new \Providers\Stripe\Stripe($secretKey);
// or via service:
$stripe = service('subscriptionPayment', 'stripe');
$stripe = service('oneTimePayment', 'stripe');
```

Key is read from `billing.stripe_secret_key` (AES-256-CTR encrypted in settings). On local also falls back to `env('STRIPE_SECRET_KEY')`.

---

## HTTP Layer

All requests use `\Config\Services::curlrequest()` (CI4 cURL wrapper).

**Base config:**
- `baseURI` = `https://api.stripe.com/v1`
- `timeout` = 30s
- `http_errors` = false (status handled manually in `parseResponse()`)
- `verify` = `ENVIRONMENT === 'production'` — SSL verification only in production

**Headers:**
- `Authorization: Bearer {secretKey}` — all requests
- `Content-Type: application/x-www-form-urlencoded` — POST requests
- `Idempotency-Key: {key}` — mutating requests where double-execution would cause problems

**Response handling (`parseResponse()`):**
- 2xx → return decoded JSON array
- 4xx → throw `\RuntimeException` with `error.message`; log via `l([...], 'stripe_errors')`
- 5xx → throw `\RuntimeException`; log via `l([...], 'stripe_errors')`

Network timeouts and cURL errors propagate as exceptions automatically through CI4.

---

## Parameter Encoding

Stripe accepts `application/x-www-form-urlencoded` with PHP-style nested keys:
```
line_items[0][price]=price_xxx&line_items[0][quantity]=1
```

`flattenParams(array $params, string $prefix = '')` recursively flattens nested PHP arrays:
```php
['line_items' => [['price' => 'x', 'quantity' => 1]]]
// → ['line_items[0][price]' => 'x', 'line_items[0][quantity]' => 1]
```

Used only in `checkout()`. All other methods accept already-flat params.

---

## Methods

### `checkout(array $params): array`
Creates a Stripe Checkout Session. Params are passed directly to the Stripe API after `flattenParams()`.

**Subscription (`mode=subscription`):**
```php
$session = $stripe->checkout([
    'mode'              => 'subscription',
    'customer'          => 'cus_xxx',
    'line_items'        => [['price' => 'price_xxx', 'quantity' => 1]],
    'success_url'       => 'https://...',
    'cancel_url'        => 'https://...',
    'metadata'          => ['user_id' => 1, 'plan_id' => 2, 'type' => 'subscription'],
    'subscription_data' => ['trial_period_days' => 7],  // if trial
    'discounts'         => [['coupon' => 'coup_xxx']],  // if promo code
]);
redirect()->to($session['url']);
```

**One-time payment (`mode=payment`):**
```php
$session = $stripe->checkout([
    'mode'                => 'payment',
    'customer'            => 'cus_xxx',
    'line_items'          => [['price_data' => [
        'currency'     => 'usd',
        'unit_amount'  => 2900,  // cents
        'product_data' => ['name' => '500 Credits'],
    ], 'quantity' => 1]],
    'payment_intent_data' => ['setup_future_usage' => 'off_session'],  // save card
    'success_url'         => 'https://...',
    'cancel_url'          => 'https://...',
    'metadata'            => ['user_id' => 1, 'package_id' => 3, 'type' => 'package'],
]);
```

Returns the session object. Use `$session['url']` for redirect.

---

### `customer(string $email, string $name, array $meta = []): array`
Creates a Stripe Customer.

```php
$customer = $stripe->customer('user@example.com', 'John Doe', ['user_id' => 42]);
// $customer['id'] = 'cus_xxx'
```

`meta` is passed as `metadata[key]=value` — visible in Stripe Dashboard.

**Important:** Create once and store in `billing_provider_refs(entity_type=customer, entity_id=user_id)`. Always go through `getOrCreateCustomer()` helper in the controller — never call this directly without checking for an existing ref first.

---

### `portal(string $customerId, string $returnUrl): array`
Creates a Stripe Billing Portal Session. The user can manage cards, view invoices, and cancel their subscription in the Stripe-hosted interface.

```php
$session = $stripe->portal('cus_xxx', 'https://mysite.com/billing');
redirect()->to($session['url']);
```

Requires Portal configuration in Stripe Dashboard → Settings → Customer portal.

---

### `cancelAtPeriodEnd(string $subscriptionId): array`
Sets `cancel_at_period_end=true` on the Stripe subscription. The subscription stays active until the end of the current billing period, then Stripe fires `customer.subscription.deleted`.

```php
$stripe->cancelAtPeriodEnd('sub_xxx');
```

Locally: set `billing_subscriptions.cancel_at_period_end = 1`. No cron needed — Stripe handles the actual termination and fires the webhook.

**vs. `cancelSubscription()`:** `cancelSubscription()` terminates immediately with no grace period. `cancelAtPeriodEnd()` gives the user access until the paid period expires.

---

### `updateSubscription(string $subId, string $newPriceId, string $collectionMethod = 'send_invoice'): string`
Changes the plan or interval on an existing Stripe subscription (upgrade/downgrade). Returns the `hosted_invoice_url` for `send_invoice` mode, empty string for `charge_automatically`.

**Algorithm:**
1. GET `/subscriptions/{subId}` — find the current `items.data[0].id` (item ID required for update)
2. POST `/subscriptions/{subId}`:
   ```
   items[0][id]       = {currentItemId}
   items[0][price]    = {newPriceId}
   proration_behavior = create_prorations
   collection_method  = send_invoice | charge_automatically
   days_until_due     = 1  (send_invoice only — needed for hosted_invoice_url to be available)
   ```
3. `Idempotency-Key: upgrade-{subId}-{newPriceId}` — protects against duplicate upgrades

**`send_invoice` mode:**
- Stripe creates a proration invoice
- Returns `hosted_invoice_url` → controller redirects user there for manual payment
- If `latest_invoice` is already an expanded object, URL is read directly; if it's a string ID, fetch via GET `/invoices/{id}`

**`charge_automatically` mode:**
- Stripe immediately charges the card on file
- Returns empty string
- Controller shows a flash success message

**Important:** The old subscription is NOT cancelled in `updateSubscription()` — Stripe updates items on the same subscription object. Credit adjustment happens via the `invoice.paid` webhook with `billing_reason=subscription_update` → `BillingService::upgradeCredits()`.

---

### `createCoupon(string $name, string $discountType, float $discountValue, string $duration): array`
Creates a Stripe Coupon for subscription discounts.

```php
$coupon = $stripe->createCoupon('PROMO-SUMMER50', 'percent', 50.0, 'once');
// $coupon['id'] = 'coup_xxx'
```

| Parameter | Maps to |
|---|---|
| `discountType = percent` | `percent_off = discountValue` |
| `discountType = fixed` | `amount_off = (int)discountValue`, `currency = usd` |
| `duration = once` | discount on first payment only |
| `duration = forever` | discount on all subscription payments |

**Why Coupon instead of reduced unit_amount:** If you pass a lowered `unit_amount` to a Stripe Price, the subscription is created at that price permanently. A Coupon is applied on top of the standard price and controlled via `duration`.

**Deduplication:** Before creating a new Coupon, always check `billing_provider_refs(entity_type=promotion, provider=stripe)`. One local promo code → one Stripe Coupon. Done in `Subscribe::getOrCreateCoupon()`.

---

### `createOrUpdateProduct(string $name, string $description): array`
Creates a Stripe Product. Always creates a new product — the caller decides whether to create or reuse an existing one by checking `billing_provider_refs`.

```php
$product = $stripe->createOrUpdateProduct('Pro Plan', 'For growing teams');
// $product['id'] = 'prod_xxx'
```

Stripe Products are mutable (name/description can be updated), but the method doesn't update them — `syncProvider()` handles this by passing a known product ID when one already exists.

---

### `createPrice(string $productId, int $unitAmount, string $currency, string $interval): array`
Creates a Stripe Price attached to a product.

```php
$price = $stripe->createPrice('prod_xxx', 2900, 'usd', 'month');
// $price['id'] = 'price_xxx'
```

**Interval mapping:**

| Local interval | Stripe `interval` | `interval_count` |
|---|---|---|
| `month` | `month` | 1 |
| `quarter` | `month` | 3 |
| `halfyear` | `month` | 6 |
| `year` | `year` | 1 |

**Important:** Stripe Prices are **immutable** — `unit_amount`, `currency`, and `interval` cannot be changed after creation. When a local price changes, the old Stripe Price must be archived and a new one created via `archivePrice()` + `createPrice()`. This is handled automatically by `syncProvider()`.

---

### `archivePrice(string $stripePriceId): void`
Deactivates a Stripe Price by setting `active=false`. An archived price cannot be used for new subscriptions, but existing subscriptions continue working.

```php
$stripe->archivePrice('price_old_xxx');
```

Used in `Install::syncProvider()` when a local price has changed — archive the old one, create a new one, update the ref and snapshot.

---

### `charge(int $amount, string $currency, array $options = []): array`
Creates a Stripe PaymentIntent with `confirm=true`. Low-level method — in the billing flow use `checkout()` instead. Direct use case: off-session charges with a known payment method ID.

---

### `refund(string $chargeId, int $amount = 0): array`
Issues a refund via Stripe Refunds API.

```php
$stripe->refund('ch_xxx', 1450);  // partial refund of $14.50
$stripe->refund('ch_xxx');        // full refund
```

`$amount` in cents. If 0 — Stripe issues a full refund. The `charge_id` is stored in `billing_invoices.data` JSON when the `checkout.session.completed` or `charge.refunded` webhook is processed.

---

### `createSubscription(string $customerId, string $priceId, array $options = []): array`
Creates a subscription directly via the Subscriptions API (no Checkout page). Not used in the current billing flow — `checkout()` with `mode=subscription` is used instead. Available for non-standard scenarios where you need to create a subscription server-side.

---

### `cancelSubscription(string $subscriptionId): array`
Immediately cancels a subscription. Not used in the billing flow — `cancelAtPeriodEnd()` is used instead to give users a grace period. Use only when immediate termination is required.

---

## Webhook Configuration

Register this URL in Stripe Dashboard → Webhooks:
```
https://yoursite.com/api/v1/billing/webhook/stripe/{billing.webhook_secret_stripe}
```

The token is auto-generated on first settings save (Admin → Billing → Setup) and can be regenerated via the "Regenerate" button.

**Required events:**
- `checkout.session.completed`
- `invoice.paid`
- `invoice.payment_failed`
- `customer.subscription.updated`
- `customer.subscription.deleted`
- `charge.refunded`

**Signature verification** (`StripeHandler::verifySignature()`):
1. Parse `Stripe-Signature: t=...,v1=...` header
2. Check timestamp is within 5 minutes (replay attack protection)
3. Compute `HMAC-SHA256("{timestamp}.{payload}", billing.stripe_webhook_secret)`
4. Compare with `v1` using `hash_equals()` (constant-time comparison)

Returns 400 on failure — no detail in the response body.

---

## Local Development

SSL verification is disabled on non-production (`'verify' => ENVIRONMENT === 'production'`), so `localhost` works without certificate setup.

For webhook testing without a public URL, use Stripe CLI:
```bash
stripe listen --forward-to localhost/api/v1/billing/webhook/stripe/{token}
stripe trigger checkout.session.completed
```

Test cards:
- `4242 4242 4242 4242` — successful payment
- `4000 0000 0000 3220` — requires 3D Secure
- `4000 0000 0000 9995` — insufficient funds

---

## Known Constraints and Edge Cases

**Stripe Prices are immutable.** Never attempt to update `unit_amount`, `currency`, or `interval` on an existing price — it will fail. Any local price change requires Sync All in Setup to archive the old price and create a new one.

**One Customer per user.** A single Customer ID is stored per user in `billing_provider_refs`. If a Customer is deleted manually in the Stripe Dashboard, the next checkout will fail. Fix: delete the row from `billing_provider_refs` for `entity_type=customer` and the next checkout will recreate it.

**`updateSubscription` requires the item ID.** You cannot update a subscription by passing only the new price — Stripe requires `items[0][id]={currentItemId}`. That's why the method does a GET request first.

**`send_invoice` requires `days_until_due`.** Without it Stripe may not generate `hosted_invoice_url` immediately. Set to `1` — this is the minimum and gives enough time for the redirect.

**Proration on upgrade.** `proration_behavior=create_prorations` — Stripe calculates the price difference automatically. The resulting `invoice.paid` webhook with `billing_reason=subscription_update` triggers `upgradeCredits()` in the handler, which adds only the proportional credits for remaining days (never deducts).

**Trial + Coupon combination.** Both are accepted by Stripe. The `duration=once` coupon applies to the first actual payment — which is the first invoice AFTER the trial ends, not the $0 trial checkout.

**100% discount on subscription.** Stripe still shows a Checkout with $0 but requires a card. The Coupon with `percent_off=100, duration=once` applies only to the first payment; subsequent renewals charge full price. Never bypass this with a local shortcut — the card must be on file in Stripe for future charges.

**`charge_id` in invoice data.** The refund flow relies on `billing_invoices.data` containing `{"charge_id": "ch_xxx"}`. This is written by `StripeHandler` when processing `checkout.session.completed` or `invoice.paid`. If a refund is requested on an invoice without a `charge_id`, the admin refund form will show an error.
