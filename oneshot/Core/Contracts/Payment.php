<?php

namespace OneShot\Core\Contracts;

interface Payment
{
    public function charge(int $amount, string $currency, array $options = []): array;
    public function refund(string $chargeId, int $amount = 0): array;
    public function createSubscription(string $customerId, string $planId, array $options = []): array;
    public function cancelSubscription(string $subscriptionId): array;
}
