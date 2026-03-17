<?php

namespace OneShot\Billing\Models;

use OneShot\Core\Models\Base;

class Package extends Base
{
    protected $table         = 'billing_packages';
    protected $allowedFields = [
        'name', 'credits', 'price', 'old_price', 'badge', 'currency', 'is_active', 'sort',
    ];

    public function getActive(): array
    {
        return $this->where('is_active', 1)
                    ->where('deleted_at IS NULL')
                    ->orderBy('sort', 'ASC')
                    ->findAll();
    }
}
