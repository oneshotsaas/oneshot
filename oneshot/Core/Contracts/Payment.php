<?php

namespace OneShot\Core\Contracts;

interface Payment
{
    public function charge(int $amount, string $currency, array $options = []): array;
    public function refund(string $chargeId, int $amount = 0): array;
    public function createSubscription(string $customerId, string $planId, array $options = []): array;
    public function cancelSubscription(string $subscriptionId): array;

    public function checkout(array $params): array;
    public function customer(string $email, string $name, array $meta = []): array;
    public function portal(string $customerId, string $returnUrl): array;
    public function cancelAtPeriodEnd(string $subscriptionId): array;
    public function updateSubscription(string $subId, string $newPriceId, string $collectionMethod = 'send_invoice'): string;
    public function createCoupon(string $name, string $discountType, float $discountValue, string $duration): array;
    public function createOrUpdateProduct(string $name, string $description): array;
    public function createPrice(string $productId, int $unitAmount, string $currency, string $interval): array;
    public function archivePrice(string $stripePriceId): void;
}
