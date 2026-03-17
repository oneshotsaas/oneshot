<?php

namespace OneShot\Billing\Models;

use OneShot\Core\Models\Base;

class Plan extends Base
{
    protected $table      = 'billing_plans';
    protected $allowedFields = [
        'name', 'slug', 'description', 'credits_included', 'trial_days',
        'features', 'badge', 'hide_price', 'is_active', 'sort',
    ];

    public function getActive(): array
    {
        return $this->where('is_active', 1)
                    ->where('deleted_at IS NULL')
                    ->orderBy('sort', 'ASC')
                    ->findAll();
    }
}
