<?php

namespace OneShot\Billing\Webhooks;

use OneShot\Billing\Services\BillingService;
use OneShot\Billing\Models\{Subscription, Invoice, Plan, PlanPrice};

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
            case 'checkout.session.completed':
                $this->onCheckoutCompleted($billing, $object);
                break;

            case 'invoice.paid':
                $this->onInvoicePaid($billing, $object);
                break;

            case 'customer.subscription.updated':
                $this->onSubscriptionUpdated($billing, $object);
                break;

            case 'customer.subscription.deleted':
                $this->onSubscriptionDeleted($object);
                break;

            case 'invoice.payment_failed':
                $this->onPaymentFailed($billing, $object);
                break;

            case 'charge.refunded':
                $this->onChargeRefunded($billing, $object);
                break;
        }
    }

    private function onCheckoutCompleted(BillingService $billing, array $object): void
    {
        $mode     = $object['mode'] ?? '';
        $metadata = $object['metadata'] ?? [];
        $userId   = (int)($metadata['user_id'] ?? 0);

        if (!$userId) {
            return;
        }

        if ($mode === 'subscription') {
            $planId    = (int)($metadata['plan_id'] ?? 0);
            $interval  = $metadata['interval'] ?? 'month';
            $promoCode = $metadata['promo_code'] ?: null;

            try {
                $sub = $billing->subscribe($userId, $planId, $interval, $promoCode);
                $billing->setProviderRef('subscription', $sub->id, 'stripe', $object['subscription']);
                $billing->setProviderRef('customer', $userId, 'stripe', $object['customer']);

                // Mark open invoice as paid
                $invoice = (new Invoice())->where('subscription_id', $sub->id)->where('status', 'open')->orderBy('id', 'DESC')->limit(1)->first();
                if ($invoice) {
                    \Config\Database::connect()->table('billing_invoices')->where('id', $invoice->id)->update(['status' => 'paid', 'paid_at' => date('Y-m-d H:i:s')]);
                }

                event('billing.subscribed', ['user_id' => $userId, 'plan_id' => $planId, 'interval' => $interval]);
                notify($userId, 'billing.invoice_paid', __('billing.notify_invoice_paid', 'Payment confirmed'), site_url(route_to('billing.invoices')), ['credits' => 0]);

            } catch (\RuntimeException $e) {
                l(['user_id' => $userId, 'error' => $e->getMessage(), 'event' => 'checkout.session.completed'], 'billing_webhook');
            }

        } elseif ($mode === 'payment') {
            $packageId = (int)($metadata['package_id'] ?? 0);
            $promoCode = $metadata['promo_code'] ?: null;

            if (!$packageId) {
                return;
            }

            try {
                $invoice = $billing->purchasePackage($userId, $packageId, $promoCode);
                $billing->setProviderRef('customer', $userId, 'stripe', $object['customer']);

                // Mark paid + grant credits
                \Config\Database::connect()->table('billing_invoices')->where('id', $invoice->id)->update(['status' => 'paid', 'paid_at' => date('Y-m-d H:i:s')]);

                $package = (new \OneShot\Billing\Models\Package())->getById($packageId);
                if ($package) {
                    $billing->add($userId, (int)$package->credits, 'grant', [
                        'description'  => 'Package: ' . $package->name,
                        'credit_source'=> 'package',
                        'ref_type'     => 'invoice',
                        'ref_id'       => $invoice->id,
                    ]);

                    event('billing.package_purchased', ['user_id' => $userId, 'package_id' => $packageId, 'credits' => $package->credits]);
                    notify($userId, 'billing.package_purchased', sprintf(__('billing.notify_package_purchased', 'Package purchased — +%s credits added'), $package->credits), site_url(route_to('billing.invoices')), ['credits' => $package->credits]);
                }

            } catch (\RuntimeException $e) {
                l(['user_id' => $userId, 'error' => $e->getMessage(), 'event' => 'checkout.session.completed', 'mode' => 'payment'], 'billing_webhook');
            }
        }
    }

    private function onInvoicePaid(BillingService $billing, array $object): void
    {
        $stripeSubId    = $object['subscription'] ?? null;
        $billingReason  = $object['billing_reason'] ?? '';
        $stripeInvoiceId = $object['id'] ?? null;

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

        $subId = (int)$ref->entity_id;
        $sub   = (new Subscription())->getById($subId);
        if (!$sub) {
            return;
        }

        if ($billingReason === 'subscription_update') {
            // Upgrade flow: grant proportional credits
            $stripeSub     = $object['lines']['data'][0] ?? [];
            $newPriceId    = $stripeSub['price']['id'] ?? null;
            $periodEnd     = $object['lines']['data'][0]['period']['end'] ?? null;
            $periodStart   = $object['lines']['data'][0]['period']['start'] ?? null;

            // Find new plan from price ref
            $priceRef = $db->table('billing_provider_refs')
                           ->where('provider', 'stripe')
                           ->where('ref_id', $newPriceId)
                           ->where('entity_type', 'plan_price')
                           ->limit(1)->get()->getRowObject();

            if ($priceRef) {
                $planPrice = (new PlanPrice())->getById((int)$priceRef->entity_id);
                if ($planPrice) {
                    $totalDays     = $periodStart && $periodEnd ? (int)(($periodEnd - $periodStart) / 86400) : 30;
                    $remainingDays = $periodEnd ? (int)(($periodEnd - time()) / 86400) : 0;

                    // Map webhook invoice id to a local one
                    $localInvoiceRef = $db->table('billing_provider_refs')
                                          ->where('provider', 'stripe')
                                          ->where('ref_id', $stripeInvoiceId)
                                          ->where('entity_type', 'invoice')
                                          ->limit(1)->get()->getRowObject();
                    $localInvoiceId = $localInvoiceRef ? (int)$localInvoiceRef->entity_id : null;

                    $billing->upgradeCredits($subId, (int)$planPrice->plan_id, $remainingDays, $totalDays, $localInvoiceId);

                    // Update local subscription plan
                    $db->table('billing_subscriptions')->where('id', $subId)->update([
                        'plan_id'       => $planPrice->plan_id,
                        'plan_price_id' => $planPrice->id,
                        'updated_at'    => date('Y-m-d H:i:s'),
                    ]);

                    event('billing.subscription_upgraded', ['user_id' => $sub->user_id, 'sub_id' => $subId]);
                    notify((int)$sub->user_id, 'billing.subscription_upgraded', __('billing.notify_subscription_upgraded', 'Subscription upgraded'), site_url(route_to('billing.index')), []);
                }
            }
            return;
        }

        // Standard renewal
        $invoice = (new Invoice())->where('subscription_id', $subId)
                                  ->where('status', 'open')
                                  ->orderBy('id', 'DESC')
                                  ->limit(1)->first();

        if ($invoice) {
            $billing->renewCredits($subId, $invoice->id);
            event('billing.invoice_paid', ['user_id' => $sub->user_id, 'sub_id' => $subId, 'invoice_id' => $invoice->id]);
            notify((int)$sub->user_id, 'billing.invoice_paid', __('billing.notify_invoice_paid', 'Payment confirmed'), site_url(route_to('billing.invoices')), []);
        }
    }

    private function onSubscriptionUpdated(BillingService $billing, array $object): void
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
            'active'             => 'active',
            'trialing'           => 'trial',
            'past_due'           => 'past_due',
            'canceled'           => 'canceled',
            'incomplete'         => 'past_due',
            'incomplete_expired' => 'canceled',
        ];
        $newStatus = $statusMap[$object['status'] ?? ''] ?? null;
        if (!$newStatus) {
            return;
        }

        $updateData = [
            'status'               => $newStatus,
            'current_period_start' => date('Y-m-d H:i:s', $object['current_period_start'] ?? time()),
            'current_period_end'   => date('Y-m-d H:i:s', $object['current_period_end'] ?? time()),
            'updated_at'           => date('Y-m-d H:i:s'),
        ];

        // Reflect cancel_at_period_end from Stripe
        if (isset($object['cancel_at_period_end'])) {
            $updateData['cancel_at_period_end'] = $object['cancel_at_period_end'] ? 1 : 0;
        }

        // If subscription was reactivated (cancel_at_period_end removed)
        if (isset($object['cancel_at_period_end']) && !$object['cancel_at_period_end']) {
            $updateData['cancel_at_period_end'] = 0;
        }

        $db->table('billing_subscriptions')->where('id', $ref->entity_id)->update($updateData);
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

        $sub = (new Subscription())->getById((int)$ref->entity_id);
        if ($sub) {
            event('billing.subscription_deleted', ['user_id' => $sub->user_id]);
        }
    }

    private function onPaymentFailed(BillingService $billing, array $object): void
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

        $sub = (new Subscription())->getById((int)$ref->entity_id);
        if ($sub) {
            event('billing.payment_failed', ['user_id' => $sub->user_id]);
            notify((int)$sub->user_id, 'billing.payment_failed', __('billing.notify_payment_failed', 'Payment failed — please update your payment method'), site_url(route_to('billing.portal')), []);
        }
    }

    private function onChargeRefunded(BillingService $billing, array $object): void
    {
        $chargeId  = $object['id'] ?? null;
        $amountRefunded = (int)($object['amount_refunded'] ?? 0);
        $amountTotal    = (int)($object['amount'] ?? 0);

        if (!$chargeId) {
            return;
        }

        // Find invoice by charge id stored in data JSON
        $db = \Config\Database::connect();
        $invoiceRow = $db->table('billing_invoices')
                         ->like('data', $chargeId)
                         ->where('status !=', 'refunded')
                         ->limit(1)->get()->getRowObject();

        if (!$invoiceRow) {
            return;
        }

        // Mark invoice refunded
        $db->table('billing_invoices')->where('id', $invoiceRow->id)->update([
            'status'     => 'refunded',
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        // Calculate credits to deduct (proportional strategy)
        $creditsGranted = (float)$db->table('billing_transactions')
                                    ->selectSum('amount')
                                    ->where('ref_type', 'invoice')
                                    ->where('ref_id', $invoiceRow->id)
                                    ->where('amount >', 0)
                                    ->get()->getRowObject()->amount;

        if ($creditsGranted > 0 && $amountTotal > 0) {
            $ratio   = $amountRefunded / $amountTotal;
            $deduct  = round($creditsGranted * $ratio);

            if ($deduct > 0) {
                $billing->deduct((int)$invoiceRow->user_id, $deduct, [
                    'type'        => 'refund',
                    'description' => 'Refund: charge ' . $chargeId,
                    'ref_type'    => 'invoice',
                    'ref_id'      => $invoiceRow->id,
                    'force'       => true,
                ]);
            }
        }

        event('billing.refund_issued', ['user_id' => $invoiceRow->user_id, 'invoice_id' => $invoiceRow->id]);
        notify((int)$invoiceRow->user_id, 'billing.refund_issued', __('billing.notify_refund_issued', 'Refund issued'), site_url(route_to('billing.invoices')), []);
    }
}
