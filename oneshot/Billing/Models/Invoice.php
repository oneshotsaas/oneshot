<?php

namespace OneShot\Billing\Models;

use OneShot\Core\Models\Base;

class Invoice extends Base
{
    protected $table         = 'billing_invoices';
    protected $allowedFields = [
        'user_id', 'subscription_id', 'package_id', 'promotion_id',
        'amount', 'amount_discount', 'currency', 'status', 'description',
        'paid_at', 'tax_amount', 'tax_rate', 'tax_source',
        'retry_count', 'next_retry_at', 'data',
    ];

    public function getForUser(int $userId, int $perPage = 30): array
    {
        return $this->where('user_id', $userId)
                    ->where('deleted_at IS NULL')
                    ->orderBy('id', 'DESC')
                    ->paginate($perPage);
    }
}
