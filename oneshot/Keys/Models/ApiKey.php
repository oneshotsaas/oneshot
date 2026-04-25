<?php

namespace OneShot\Keys\Models;

class ApiKey extends \OneShot\Core\Models\Base
{
    protected $table          = 'keys_keys';
    protected $useSoftDeletes = true;
    protected $useTimestamps  = true;
    protected $allowedFields  = [
        'user_id', 'name', 'key_id', 'key_hash', 'key_suffix',
        'expires_at', 'status', 'limits_requests', 'limits_credits', 'last_used_at',
    ];

    /**
     * SELECT ... FOR UPDATE by key_id — called inside a transaction.
     */
    public function findByKeyIdForUpdate(string $keyId): object|null
    {
        $db = \Config\Database::connect();
        $result = $db->query(
            'SELECT * FROM keys_keys WHERE key_id = ? AND deleted_at IS NULL LIMIT 1 FOR UPDATE',
            [$keyId]
        );
        return $result->getRow() ?: null;
    }

    public function getLimits(object $key, string $field): array
    {
        return json_decode($key->{$field}, true) ?? [];
    }

    public function forUser(int $userId): array
    {
        return $this->where('user_id', $userId)->findAll();
    }

    public function findByIdForUser(int $id, int $userId): object|null
    {
        return $this->where('id', $id)->where('user_id', $userId)->first();
    }
}
