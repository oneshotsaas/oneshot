<?php

namespace OneShot\Keys\Models;

class Usage extends \OneShot\Core\Models\Base
{
    protected $table          = 'keys_usage';
    protected $useSoftDeletes = false;
    protected $useTimestamps  = true;
    protected $allowedFields  = ['key_id', 'usage_date', 'requests', 'credits'];

    public function increment(int $keyId, int $credits = 0): void
    {
        $db = \Config\Database::connect();
        $db->query(
            'INSERT INTO keys_usage (key_id, usage_date, requests, credits, created_at, updated_at)
             VALUES (?, DATE_FORMAT(UTC_TIMESTAMP(), \'%Y-%m-%d 00:00:00\'), 1, ?, UTC_TIMESTAMP(), UTC_TIMESTAMP())
             ON DUPLICATE KEY UPDATE requests = requests + 1, credits = credits + ?, updated_at = UTC_TIMESTAMP()',
            [$keyId, $credits, $credits]
        );
    }

    public function sumForKey(int $keyId, int $days = 0): object
    {
        $db = \Config\Database::connect();

        if ($days > 0) {
            $cutoff = (new \DateTimeImmutable('now', new \DateTimeZone('UTC')))
                ->modify('-' . ($days - 1) . ' days')
                ->format('Y-m-d 00:00:00');

            $result = $db->query(
                'SELECT COALESCE(SUM(requests), 0) AS requests, COALESCE(SUM(credits), 0) AS credits
                 FROM keys_usage WHERE key_id = ? AND usage_date >= ?',
                [$keyId, $cutoff]
            );
        } else {
            $result = $db->query(
                'SELECT COALESCE(SUM(requests), 0) AS requests, COALESCE(SUM(credits), 0) AS credits
                 FROM keys_usage WHERE key_id = ?',
                [$keyId]
            );
        }

        return $result->getRow() ?: (object)['requests' => 0, 'credits' => 0];
    }
}
