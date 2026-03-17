<?php

namespace OneShot\Billing\Models;

use OneShot\Core\Models\Base;

class Subscription extends Base
{
    protected $table         = 'billing_subscriptions';
    protected $allowedFields = [
        'user_id', 'plan_id', 'plan_price_id', 'promotion_id', 'status',
        'trial_ends_at', 'current_period_start', 'current_period_end', 'canceled_at',
        'credits_balance', 'overdraft_used', 'features_snapshot', 'is_active',
    ];

    public function getActive(int $userId): ?object
    {
        return $this->where('user_id', $userId)
                    ->where('is_active', 1)
                    ->where('deleted_at IS NULL')
                    ->limit(1)->first();
    }

    public function getHistory(int $userId): array
    {
        return $this->where('user_id', $userId)
                    ->where('deleted_at IS NULL')
                    ->orderBy('id', 'DESC')
                    ->findAll();
    }
}
