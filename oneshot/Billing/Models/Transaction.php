<?php

namespace OneShot\Billing\Models;

use OneShot\Core\Models\Base;

class Transaction extends Base
{
    protected $table          = 'billing_transactions';
    protected $useSoftDeletes = false;
    protected $useTimestamps  = false;
    protected $allowedFields  = [
        'user_id', 'subscription_id', 'amount', 'type', 'action', 'unit_type',
        'quantity', 'data', 'description', 'ref_type', 'ref_id', 'balance_after',
        'idempotency_key', 'ref_transaction_id', 'credit_source', 'actor_id', 'actor_type',
        'created_at',
    ];

    public function delete($id = null, bool $purge = false): bool
    {
        throw new \LogicException('billing_transactions is immutable');
    }

    public function update($id = null, $data = null): bool
    {
        throw new \LogicException('billing_transactions is immutable');
    }

    public function getLastBalance(int $userId): float
    {
        $row = $this->select('balance_after')
                    ->where('user_id', $userId)
                    ->orderBy('id', 'DESC')
                    ->limit(1)->first();

        return $row ? (float) $row->balance_after : 0.0;
    }

    public function getForUser(int $userId, array $filters = [], int $perPage = 30): array
    {
        $builder = $this->where('user_id', $userId);

        if (!empty($filters['date_from'])) {
            $builder->where('created_at >=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $builder->where('created_at <=', $filters['date_to'] . ' 23:59:59');
        }
        if (!empty($filters['action_prefix'])) {
            $builder->like('action', $filters['action_prefix'], 'after');
        }

        return $builder->orderBy('id', 'DESC')->paginate($perPage);
    }

    public function sumForUser(int $userId): float
    {
        $result = $this->selectSum('amount')->where('user_id', $userId)->first();
        return $result ? (float) $result->amount : 0.0;
    }
}
