<?php

namespace OneShot\Billing\Webhooks;

use OneShot\Billing\Services\BillingService;
use OneShot\Billing\Models\{Subscription, Invoice};

class StripeHandler
{
    private string $payload;
    private \CodeIgniter\HTTP\IncomingRequest $request;
    private array $event = [];

    public function __construct(string $payload, \CodeIgniter\HTTP\IncomingRequest $request)
    {
        $this->payload = $payload;
        $this->request = $request;
    }

    public function verifySignature(): void
    {
        $sigHeader = $this->request->getHeaderLine('Stripe-Signature');
        $secret    = option('billing.stripe_webhook_secret', '');

        if (empty($secret)) {
            throw new \RuntimeException('Stripe webhook secret not configured');
        }

        // Parse t=... v1=... from header
        $parts = [];
        foreach (explode(',', $sigHeader) as $part) {
            [$k, $v] = array_pad(explode('=', $part, 2), 2, '');
            $parts[$k] = $v;
        }

        $timestamp = $parts['t'] ?? 0;
        $v1        = $parts['v1'] ?? '';

        if (abs(time() - (int)$timestamp) > 300) {
            throw new \RuntimeException('Webhook timestamp too old');
        }

        $expected = hash_hmac('sha256', $timestamp . '.' . $this->payload, $secret);
        if (!hash_equals($expected, $v1)) {
            throw new \RuntimeException('Invalid Stripe signature');
        }

        $this->event = json_decode($this->payload, true) ?? [];
    }

    public function getEventId(): string
    {
        return $this->event['id'] ?? '';
    }

    public function getEventType(): string
    {
        return $this->event['type'] ?? '';
    }

    public function dispatch(BillingService $billing): void
    {
        $type   = $this->getEventType();
        $object = $this->event['data']['object'] ?? [];

        switch ($type) {
            case 'invoice.paid':
                $this->onInvoicePaid($billing, $object);
                break;

            case 'customer.subscription.updated':
                $this->onSubscriptionUpdated($object);
                break;

            case 'customer.subscription.deleted':
                $this->onSubscriptionDeleted($object);
                break;

            case 'invoice.payment_failed':
                $this->onPaymentFailed($object);
                break;
        }
    }

    private function onInvoicePaid(BillingService $billing, array $object): void
    {
        $stripeSubId = $object['subscription'] ?? null;
        if (!$stripeSubId) {
            return;
        }

        $ref = $billing->getProviderRef('subscription', 0, 'stripe');
        // Find local subscription by Stripe sub ID
        $db  = \Config\Database::connect();
        $ref = $db->table('billing_provider_refs')
                  ->where('provider', 'stripe')
                  ->where('ref_id', $stripeSubId)
                  ->where('entity_type', 'subscription')
                  ->limit(1)->get()->getRowObject();

        if (!$ref) {
            return;
        }

        $subId = (int) $ref->entity_id;
        $sub   = (new Subscription())->getById($subId);
        if (!$sub) {
            return;
        }

        // Find the open invoice for this subscription
        $invoice = (new Invoice())->where('subscription_id', $subId)
                                  ->where('status', 'open')
                                  ->orderBy('id', 'DESC')
                                  ->limit(1)->first();

        if ($invoice) {
            $billing->renewCredits($subId, $invoice->id);
        }
    }

    private function onSubscriptionUpdated(array $object): void
    {
        $stripeSubId = $object['id'] ?? null;
        if (!$stripeSubId) {
            return;
        }

        $db  = \Config\Database::connect();
        $ref = $db->table('billing_provider_refs')
                  ->where('provider', 'stripe')
                  ->where('ref_id', $stripeSubId)
                  ->where('entity_type', 'subscription')
                  ->limit(1)->get()->getRowObject();

        if (!$ref) {
            return;
        }

        $statusMap = [
            'active'   => 'active',
            'trialing' => 'trial',
            'past_due' => 'past_due',
            'canceled' => 'canceled',
        ];
        $newStatus = $statusMap[$object['status'] ?? ''] ?? null;
        if (!$newStatus) {
            return;
        }

        $db->table('billing_subscriptions')->where('id', $ref->entity_id)->update([
            'status'               => $newStatus,
            'current_period_start' => date('Y-m-d H:i:s', $object['current_period_start'] ?? time()),
            'current_period_end'   => date('Y-m-d H:i:s', $object['current_period_end'] ?? time()),
            'updated_at'           => date('Y-m-d H:i:s'),
        ]);
    }

    private function onSubscriptionDeleted(array $object): void
    {
        $stripeSubId = $object['id'] ?? null;
        if (!$stripeSubId) {
            return;
        }

        $db  = \Config\Database::connect();
        $ref = $db->table('billing_provider_refs')
                  ->where('provider', 'stripe')
                  ->where('ref_id', $stripeSubId)
                  ->where('entity_type', 'subscription')
                  ->limit(1)->get()->getRowObject();

        if (!$ref) {
            return;
        }

        $db->table('billing_subscriptions')->where('id', $ref->entity_id)->update([
            'status'      => 'canceled',
            'is_active'   => null,
            'canceled_at' => date('Y-m-d H:i:s'),
            'updated_at'  => date('Y-m-d H:i:s'),
        ]);
    }

    private function onPaymentFailed(array $object): void
    {
        $stripeSubId = $object['subscription'] ?? null;
        if (!$stripeSubId) {
            return;
        }

        $db  = \Config\Database::connect();
        $ref = $db->table('billing_provider_refs')
                  ->where('provider', 'stripe')
                  ->where('ref_id', $stripeSubId)
                  ->where('entity_type', 'subscription')
                  ->limit(1)->get()->getRowObject();

        if (!$ref) {
            return;
        }

        $db->table('billing_subscriptions')->where('id', $ref->entity_id)->update([
            'status'     => 'past_due',
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        // Increment retry on related open invoice
        $invoice = (new Invoice())->where('subscription_id', $ref->entity_id)
                                  ->where('status', 'open')
                                  ->orderBy('id', 'DESC')
                                  ->limit(1)->first();

        if ($invoice) {
            $db->table('billing_invoices')->where('id', $invoice->id)->update([
                'retry_count'   => $invoice->retry_count + 1,
                'next_retry_at' => date('Y-m-d H:i:s', strtotime('+1 day')),
                'updated_at'    => date('Y-m-d H:i:s'),
            ]);
        }
    }
}
