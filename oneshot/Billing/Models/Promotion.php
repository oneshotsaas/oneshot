<?php

namespace OneShot\Billing\Models;

use OneShot\Core\Models\Base;

class Promotion extends Base
{
    protected $table         = 'billing_promotions';
    protected $allowedFields = [
        'code', 'description', 'discount_type', 'discount_value',
        'applies_to', 'max_uses', 'used_count', 'valid_from', 'valid_until', 'is_active',
    ];

    public function findByCode(string $code): ?object
    {
        return $this->where('code', strtoupper($code))
                    ->where('is_active', 1)
                    ->where('deleted_at IS NULL')
                    ->limit(1)->first();
    }

    public function isValid(object $promo, string $appliesTo = 'all'): bool
    {
        $now = date('Y-m-d H:i:s');

        if ($promo->valid_from && $promo->valid_from > $now) {
            return false;
        }
        if ($promo->valid_until && $promo->valid_until < $now) {
            return false;
        }
        if ($promo->max_uses !== null && $promo->used_count >= $promo->max_uses) {
            return false;
        }
        if ($promo->applies_to !== 'all' && $promo->applies_to !== $appliesTo) {
            return false;
        }

        return true;
    }
}
