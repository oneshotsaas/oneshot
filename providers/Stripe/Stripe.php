<?php

namespace Providers\Stripe;

use OneShot\Core\Contracts\Payment;

class Stripe implements Payment
{
    public function __construct(
        private string $secretKey = ''
    ) {
        $this->secretKey = $secretKey ?: env('STRIPE_SECRET_KEY', '');
    }

    public function charge(int $amount, string $currency, array $options = []): array
    {
        // TODO: implement Stripe charge
        return [];
    }

    public function refund(string $chargeId, int $amount = 0): array
    {
        // TODO: implement Stripe refund
        return [];
    }

    public function createSubscription(string $customerId, string $planId, array $options = []): array
    {
        // TODO: implement Stripe subscription
        return [];
    }

    public function cancelSubscription(string $subscriptionId): array
    {
        // TODO: implement Stripe subscription cancel
        return [];
    }
}
