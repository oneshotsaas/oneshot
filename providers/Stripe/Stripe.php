<?php

namespace Providers\Stripe;

use OneShot\Core\Contracts\Payment;

class Stripe implements Payment
{
    private string $secretKey;
    private string $baseUrl = 'https://api.stripe.com/v1';

    public function __construct(string $secretKey = '')
    {
        $this->secretKey = $secretKey ?: env('STRIPE_SECRET_KEY', '');
    }

    // ── Core contract ────────────────────────────────────────────────────────

    public function charge(int $amount, string $currency, array $options = []): array
    {
        return $this->post('/payment_intents', array_merge([
            'amount'               => $amount,
            'currency'             => strtolower($currency),
            'confirm'              => 'true',
            'automatic_payment_methods[enabled]' => 'true',
        ], $options));
    }

    public function refund(string $chargeId, int $amount = 0): array
    {
        $params = ['charge' => $chargeId];
        if ($amount > 0) {
            $params['amount'] = $amount;
        }
        return $this->post('/refunds', $params);
    }

    public function createSubscription(string $customerId, string $priceId, array $options = []): array
    {
        return $this->post('/subscriptions', array_merge([
            'customer' => $customerId,
            'items[0][price]' => $priceId,
        ], $options));
    }

    public function cancelSubscription(string $subscriptionId): array
    {
        return $this->delete('/subscriptions/' . $subscriptionId);
    }

    // ── Extended contract ────────────────────────────────────────────────────

    public function checkout(array $params): array
    {
        // Flatten nested arrays for application/x-www-form-urlencoded
        return $this->post('/checkout/sessions', $this->flattenParams($params));
    }

    public function customer(string $email, string $name, array $meta = []): array
    {
        $params = ['email' => $email, 'name' => $name];
        foreach ($meta as $k => $v) {
            $params['metadata[' . $k . ']'] = $v;
        }
        return $this->post('/customers', $params);
    }

    public function portal(string $customerId, string $returnUrl): array
    {
        return $this->post('/billing_portal/sessions', [
            'customer'   => $customerId,
            'return_url' => $returnUrl,
        ]);
    }

    public function cancelAtPeriodEnd(string $subscriptionId): array
    {
        return $this->post('/subscriptions/' . $subscriptionId, [
            'cancel_at_period_end' => 'true',
        ]);
    }

    public function updateSubscription(string $subId, string $newPriceId, string $collectionMethod = 'send_invoice'): string
    {
        // Get current subscription to find existing item id
        $sub = $this->get('/subscriptions/' . $subId);
        $itemId = $sub['items']['data'][0]['id'] ?? null;

        if (!$itemId) {
            throw new \RuntimeException('Stripe subscription item not found: ' . $subId);
        }

        $params = [
            'items[0][id]'             => $itemId,
            'items[0][price]'          => $newPriceId,
            'proration_behavior'       => 'create_prorations',
            'collection_method'        => $collectionMethod,
        ];

        if ($collectionMethod === 'send_invoice') {
            $params['days_until_due'] = '1';
        }

        $updated = $this->post('/subscriptions/' . $subId, $params, ['Idempotency-Key: upgrade-' . $subId . '-' . $newPriceId]);

        if ($collectionMethod === 'send_invoice') {
            $invoiceId = $updated['latest_invoice'] ?? null;
            if (is_array($invoiceId)) {
                return $invoiceId['hosted_invoice_url'] ?? '';
            }
            if ($invoiceId) {
                $invoice = $this->get('/invoices/' . $invoiceId);
                return $invoice['hosted_invoice_url'] ?? '';
            }
        }

        return '';
    }

    public function createCoupon(string $name, string $discountType, float $discountValue, string $duration): array
    {
        $params = [
            'name'     => $name,
            'duration' => $duration,
        ];

        if ($discountType === 'percent') {
            $params['percent_off'] = $discountValue;
        } else {
            $params['amount_off'] = (int) $discountValue;
            $params['currency']   = 'usd';
        }

        return $this->post('/coupons', $params);
    }

    public function createOrUpdateProduct(string $name, string $description): array
    {
        // Stripe has no upsert — always create; caller handles ref storage
        return $this->post('/products', [
            'name'        => $name,
            'description' => $description ?: $name,
        ]);
    }

    public function createPrice(string $productId, int $unitAmount, string $currency, string $interval): array
    {
        $intervalMap = [
            'month'    => ['interval' => 'month', 'interval_count' => 1],
            'quarter'  => ['interval' => 'month', 'interval_count' => 3],
            'halfyear' => ['interval' => 'month', 'interval_count' => 6],
            'year'     => ['interval' => 'year',  'interval_count' => 1],
        ];

        $iv = $intervalMap[$interval] ?? ['interval' => 'month', 'interval_count' => 1];

        return $this->post('/prices', [
            'product'              => $productId,
            'unit_amount'          => $unitAmount,
            'currency'             => strtolower($currency),
            'recurring[interval]'  => $iv['interval'],
            'recurring[interval_count]' => $iv['interval_count'],
        ]);
    }

    public function archivePrice(string $stripePriceId): void
    {
        $this->post('/prices/' . $stripePriceId, ['active' => 'false']);
    }

    // ── HTTP helpers ─────────────────────────────────────────────────────────

    private function get(string $path): array
    {
        $client = \Config\Services::curlrequest([
            'timeout'     => 30,
            'http_errors' => false,
            'verify'      => ENVIRONMENT === 'production',
        ]);

        $response = $client->get($this->baseUrl . $path, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->secretKey,
            ],
        ]);

        return $this->parseResponse($response, 'GET ' . $path);
    }

    private function post(string $path, array $params, array $extraHeaders = []): array
    {
        if (empty($this->secretKey)) {
            throw new \RuntimeException('Stripe secret key is not configured');
        }

        $headers = [
            'Authorization' => 'Bearer ' . $this->secretKey,
            'Content-Type'  => 'application/x-www-form-urlencoded',
        ];

        // Extract Idempotency-Key from extraHeaders if passed as "Key: value"
        foreach ($extraHeaders as $h) {
            if (str_starts_with($h, 'Idempotency-Key:')) {
                $headers['Idempotency-Key'] = trim(substr($h, strlen('Idempotency-Key:')));
            }
        }

        $client = \Config\Services::curlrequest([
            'timeout'     => 30,
            'http_errors' => false,
            'verify'      => ENVIRONMENT === 'production',
        ]);

        $response = $client->post($this->baseUrl . $path, [
            'headers' => $headers,
            'body'    => http_build_query($params),
        ]);

        return $this->parseResponse($response, 'POST ' . $path);
    }

    private function delete(string $path): array
    {
        $client = \Config\Services::curlrequest([
            'timeout'     => 30,
            'http_errors' => false,
            'verify'      => ENVIRONMENT === 'production',
        ]);

        $response = $client->delete($this->baseUrl . $path, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->secretKey,
            ],
        ]);

        return $this->parseResponse($response, 'DELETE ' . $path);
    }

    private function parseResponse(\CodeIgniter\HTTP\ResponseInterface $response, string $context): array
    {
        $status = $response->getStatusCode();
        $body   = json_decode($response->getBody(), true) ?? [];

        if ($status >= 500) {
            l(['context' => $context, 'status' => $status, 'body' => $body], 'stripe_errors');
            throw new \RuntimeException('Stripe server error (' . $status . '): ' . ($body['error']['message'] ?? 'unknown error'));
        }

        if ($status >= 400) {
            $msg = $body['error']['message'] ?? 'Stripe request failed';
            l(['context' => $context, 'status' => $status, 'body' => $body], 'stripe_errors');
            throw new \RuntimeException($msg);
        }

        return $body;
    }

    /**
     * Flatten nested PHP array into Stripe's form-encoded format.
     * ['line_items' => [['price' => 'x', 'quantity' => 1]]]
     * → ['line_items[0][price]' => 'x', 'line_items[0][quantity]' => 1]
     */
    private function flattenParams(array $params, string $prefix = ''): array
    {
        $result = [];
        foreach ($params as $key => $value) {
            $fullKey = $prefix !== '' ? $prefix . '[' . $key . ']' : $key;
            if (is_array($value)) {
                $result = array_merge($result, $this->flattenParams($value, $fullKey));
            } else {
                $result[$fullKey] = $value;
            }
        }
        return $result;
    }
}
