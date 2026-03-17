<?php

namespace OneShot\Billing\Models;

use OneShot\Core\Models\Base;

class PlanPrice extends Base
{
    protected $table         = 'billing_plan_prices';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'plan_id', 'interval', 'price', 'old_price', 'promo_price',
        'promo_ends_at', 'promo_ends_days', 'currency', 'discount_pct', 'promo_discount_pct',
    ];

    public function getForPlan(int $planId): array
    {
        return $this->where('plan_id', $planId)->orderBy('price', 'ASC')->findAll();
    }

    public function getByInterval(int $planId, string $interval): ?object
    {
        return $this->where('plan_id', $planId)->where('interval', $interval)->limit(1)->first();
    }
}
