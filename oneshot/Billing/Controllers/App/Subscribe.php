<?php

namespace OneShot\Billing\Controllers\App;

use OneShot\Billing\Models\{Plan, PlanPrice};

class Subscribe extends Billing
{
    public function checkout(string $hash): string
    {
        $planId = signedId($hash);
        $plan   = (new Plan())->getById($planId);
        if (!$plan || !$plan->is_active) {
            return $this->redirectWith(route_to('billing.plans'), __('billing.not_found', 'Not found'), 'error');
        }

        $priceModel       = new PlanPrice();
        $prices           = $priceModel->getForPlan($planId);
        $selectedInterval = $this->request->getGet('interval') ?? ($prices[0]->interval ?? 'month');
        $promoCode        = $this->request->getGet('promo_code') ?? '';

        $this->appendBC(__('billing.subscribe', 'Subscribe'));

        return $this->render('Billing::app/subscribe/checkout', [
            'plan'             => $plan,
            'prices'           => $prices,
            'selectedInterval' => $selectedInterval,
            'promoCode'        => $promoCode,
        ]);
    }

    public function store(string $hash): \CodeIgniter\HTTP\RedirectResponse
    {
        // Rate limit: 5 attempts per minute per user
        $userId = session()->get('user_id');
        $throttler = service('throttler');
        if (!$throttler->check('billing_subscribe_' . $userId, 5, MINUTE)) {
            return $this->redirectWith(route_to('billing.subscribe', $hash), __('billing.too_many_requests', 'Too many attempts. Please wait a moment.'), 'error');
        }

        $planId   = signedId($hash);
        $interval = $this->request->getPost('interval');
        $plan     = (new Plan())->getById($planId);
        if (!$plan) {
            return $this->redirectWith(route_to('billing.plans'), __('billing.not_found', 'Not found'), 'error');
        }

        $promoCode = $this->request->getPost('promo_code') ?: null;
        $planPrice = $interval ? (new PlanPrice())->getByInterval($planId, $interval) : null;

        // Free plan: no prices defined — subscribe locally without payment provider
        if (!$planPrice && (new PlanPrice())->where('plan_id', $planId)->countAllResults() === 0) {
            try {
                $this->billing->subscribe($userId, $planId, 'month', null);
                return $this->redirectWith(route_to('billing.subscribe.success'), __('billing.subscribed', 'Subscribed successfully'));
            } catch (\RuntimeException $e) {
                return $this->redirectWith(route_to('billing.subscribe', $hash), $e->getMessage(), 'error');
            }
        }

        if (!$planPrice) {
            return $this->redirectWith(route_to('billing.subscribe', $hash), __('billing.no_price_found', 'Pricing not found for selected interval'), 'error');
        }

        // Determine provider
        $providers = array_filter(array_map('trim', explode(',', option('billing.subscription_provider', 'stripe'))));
        $provider  = $providers[0] ?? 'stripe';

        // Fallback: no key + non-production → local subscription
        if (empty(option('billing.stripe_secret_key', '')) && ENVIRONMENT !== 'production') {
            try {
                $this->billing->subscribe($userId, $planId, $interval, $promoCode);
                return $this->redirectWith(route_to('billing.subscribe.success'), __('billing.subscribed', 'Subscribed successfully'));
            } catch (\RuntimeException $e) {
                return $this->redirectWith(route_to('billing.subscribe', $hash), $e->getMessage(), 'error');
            }
        }

        try {
            $stripeProvider = service('subscriptionPayment', $provider);

            // Check for existing active Stripe subscription (upgrade flow)
            $existingSub = $this->billing->getSubscription($userId);
            if ($existingSub && !empty($existingSub->provider)) {
                $subRef = $this->billing->getProviderRef('subscription', $existingSub->id, $existingSub->provider);
                if ($subRef) {
                    // Upgrade: update existing subscription
                    $priceRef = $this->billing->getProviderRef('plan_price', $planPrice->id, $provider);
                    if (!$priceRef) {
                        l(['user_id' => $userId, 'plan_price_id' => $planPrice->id, 'provider' => $provider], 'billing_errors');
                        return $this->redirectWith(route_to('billing.subscribe', $hash), __('billing.payment_unavailable', 'Payment is temporarily unavailable. Please try again later.'), 'error');
                    }

                    $collectionMethod = option('billing.upgrade_collection_method', 'send_invoice');
                    $hostedUrl = $stripeProvider->updateSubscription($subRef->ref_id, $priceRef->ref_id, $collectionMethod);

                    if ($collectionMethod === 'send_invoice' && $hostedUrl) {
                        return redirect()->to($hostedUrl);
                    }

                    return $this->redirectWith(route_to('billing.index'), __('billing.upgrade_invoice_sent', 'An invoice has been generated. Please complete your payment.'));
                }
            }

            // First subscription: Stripe Checkout
            $priceRef = $this->billing->getProviderRef('plan_price', $planPrice->id, $provider);
            if (!$priceRef) {
                l(['user_id' => $userId, 'plan_price_id' => $planPrice->id, 'provider' => $provider, 'context' => 'new_subscription'], 'billing_errors');
                return $this->redirectWith(route_to('billing.subscribe', $hash), __('billing.payment_unavailable', 'Payment is temporarily unavailable. Please try again later.'), 'error');
            }

            // Get or create Stripe Customer
            $customerId = $this->getOrCreateCustomer($userId, $stripeProvider, $provider);

            $sessionParams = [
                'mode'                => 'subscription',
                'customer'            => $customerId,
                'line_items'          => [['price' => $priceRef->ref_id, 'quantity' => 1]],
                'success_url'         => site_url(route_to('billing.subscribe.success') . '?session_id={CHECKOUT_SESSION_ID}'),
                'cancel_url'          => site_url(route_to('billing.subscribe.cancelled')),
                'metadata'            => [
                    'user_id'    => $userId,
                    'plan_id'    => $planId,
                    'interval'   => $interval,
                    'promo_code' => $promoCode ?? '',
                    'type'       => 'subscription',
                ],
            ];

            // Handle promo code via Stripe Coupon
            if ($promoCode) {
                $couponId = $this->getOrCreateCoupon($promoCode, $provider, $stripeProvider);
                if ($couponId) {
                    $sessionParams['discounts'] = [['coupon' => $couponId]];
                }
            }

            // Trial: if plan has trial, use Stripe trial
            if ($plan->trial_days > 0) {
                $sessionParams['subscription_data']['trial_period_days'] = (int)$plan->trial_days;
            }

            $session = $stripeProvider->checkout($sessionParams);
            return redirect()->to($session['url']);

        } catch (\RuntimeException $e) {
            l(['user_id' => $userId, 'error' => $e->getMessage()], 'billing_errors');
            return $this->redirectWith(route_to('billing.subscribe', $hash), $e->getMessage(), 'error');
        }
    }

    public function success(): string
    {
        return $this->render('Billing::app/subscribe/success');
    }

    public function cancelled(): string
    {
        return $this->render('Billing::app/subscribe/cancelled');
    }

    public function cancel(): \CodeIgniter\HTTP\RedirectResponse
    {
        $userId = session()->get('user_id');
        $sub    = $this->billing->getSubscription($userId);

        if (!$sub) {
            return $this->redirectWith(route_to('billing.index'), __('billing.not_found', 'Not found'), 'error');
        }

        // If provider subscription exists — cancel at period end
        if (!empty($sub->provider)) {
            $subRef = $this->billing->getProviderRef('subscription', $sub->id, $sub->provider);
            if ($subRef) {
                try {
                    service('subscriptionPayment', $sub->provider)->cancelAtPeriodEnd($subRef->ref_id);
                } catch (\RuntimeException $e) {
                    l(['user_id' => $userId, 'error' => $e->getMessage()], 'billing_errors');
                    return $this->redirectWith(route_to('billing.index'), $e->getMessage(), 'error');
                }
            }
        }

        // Mark locally
        \Config\Database::connect()->table('billing_subscriptions')
            ->where('id', $sub->id)
            ->update(['cancel_at_period_end' => 1, 'updated_at' => date('Y-m-d H:i:s')]);

        $until = $sub->current_period_end
            ? date('M j, Y', strtotime($sub->current_period_end))
            : '';

        notify($userId, 'billing.subscription_canceled', sprintf(__('billing.notify_subscription_canceled', 'Subscription canceled, active until %s'), $until), site_url(route_to('billing.index')), []);

        return $this->redirectWith(route_to('billing.index'), sprintf(__('billing.cancel_at_period_end', 'Subscription is active until %s'), $until));
    }

    public function portal(): \CodeIgniter\HTTP\RedirectResponse
    {
        $userId = session()->get('user_id');
        $sub    = $this->billing->getSubscription($userId);

        if (!$sub || empty($sub->provider)) {
            return $this->redirectWith(route_to('billing.index'), __('billing.not_found', 'Not found'), 'error');
        }

        $customerRef = $this->billing->getProviderRef('customer', $userId, $sub->provider);
        if (!$customerRef) {
            return $this->redirectWith(route_to('billing.index'), __('billing.not_found', 'Not found'), 'error');
        }

        try {
            $session = service('subscriptionPayment', $sub->provider)->portal(
                $customerRef->ref_id,
                site_url(route_to('billing.index'))
            );
            return redirect()->to($session['url']);
        } catch (\RuntimeException $e) {
            return $this->redirectWith(route_to('billing.index'), $e->getMessage(), 'error');
        }
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function getOrCreateCustomer(int $userId, \OneShot\Core\Contracts\Payment $provider, string $providerName): string
    {
        $ref = $this->billing->getProviderRef('customer', $userId, $providerName);
        if ($ref) {
            return $ref->ref_id;
        }

        $user = (new \OneShot\Auth\Models\User())->getById($userId);
        $result = $provider->customer($user->email, $user->name ?? $user->email, ['user_id' => $userId]);
        $this->billing->setProviderRef('customer', $userId, $providerName, $result['id']);
        return $result['id'];
    }

    private function getOrCreateCoupon(string $promoCode, string $providerName, \OneShot\Core\Contracts\Payment $provider): ?string
    {
        $promo = (new \OneShot\Billing\Models\Promotion())->findByCode($promoCode);
        if (!$promo) {
            return null;
        }

        $ref = $this->billing->getProviderRef('promotion', $promo->id, $providerName);
        if ($ref) {
            return $ref->ref_id;
        }

        $duration = $promo->subscription_discount_duration ?? 'once';
        $result = $provider->createCoupon(
            'PROMO-' . strtoupper($promoCode),
            $promo->discount_type,
            (float)$promo->discount_value,
            $duration
        );

        $this->billing->setProviderRef('promotion', $promo->id, $providerName, $result['id']);
        return $result['id'];
    }
}
