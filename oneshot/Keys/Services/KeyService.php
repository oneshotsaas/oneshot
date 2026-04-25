<?php

namespace OneShot\Keys\Services;

use OneShot\Keys\Models\ApiKey;
use OneShot\Keys\Models\Usage;

class KeyService extends \OneShot\Core\Services\Base
{
    protected ApiKey $apiKeyModel;
    protected Usage  $usageModel;

    public function __construct()
    {
        $this->apiKeyModel = new ApiKey();
        $this->usageModel  = new Usage();
    }

    protected function getPrefix(): string
    {
        return option('keys.prefix', 'oneshot_');
    }

    public function generate(
        int $userId,
        string $name,
        array $limitsRequests,
        array $limitsCredits,
        int|string|null $expires
    ): array {
        $prefix  = $this->getPrefix();
        $key_id  = bin2hex(random_bytes(7));
        $secret  = bin2hex(random_bytes(32));
        $raw     = $prefix . $key_id . ':' . $secret;
        $hash    = hash('sha256', $raw);
        $suffix  = substr($raw, -4);

        $utc = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));

        if (is_int($expires)) {
            $expires_at = $utc->modify("+{$expires} days")->format('Y-m-d H:i:s');
        } elseif (is_string($expires)) {
            $expires_at = (new \DateTimeImmutable($expires, new \DateTimeZone('UTC')))->format('Y-m-d 00:00:00');
        } else {
            $expires_at = null;
        }

        $id = $this->apiKeyModel->add([
            'user_id'         => $userId,
            'name'            => $name,
            'key_id'          => $key_id,
            'key_hash'        => $hash,
            'key_suffix'      => $suffix,
            'expires_at'      => $expires_at,
            'status'          => 'active',
            'limits_requests' => json_encode($limitsRequests),
            'limits_credits'  => json_encode($limitsCredits),
        ]);

        return ['raw' => $raw, 'id' => $id];
    }

    /**
     * Returns the key object on success.
     * On failure returns (object)['error' => true, 'status' => int, 'message' => string].
     */
    public function validateAndTrack(string $rawKey, int $credits = 0): object
    {
        $prefix = $this->getPrefix();

        if (!str_starts_with($rawKey, $prefix) || strpos(substr($rawKey, strlen($prefix)), ':') === false) {
            return $this->err(401, 'Invalid key format');
        }

        $rest   = substr($rawKey, strlen($prefix));
        $key_id = substr($rest, 0, strpos($rest, ':'));

        $db = \Config\Database::connect();
        $db->transStart();

        $key = $this->apiKeyModel->findByKeyIdForUpdate($key_id);

        if ($key === null || hash('sha256', $rawKey) !== $key->key_hash) {
            $db->transRollback();
            return $this->err(401, 'Invalid API key');
        }

        if ($key->status !== 'active') {
            $db->transRollback();
            return $this->err(401, 'Key is inactive');
        }

        if ($key->expires_at !== null) {
            $utc    = new \DateTimeZone('UTC');
            $expiry = new \DateTimeImmutable($key->expires_at, $utc);
            $now    = new \DateTimeImmutable('now', $utc);
            if ($expiry <= $now) {
                $db->transRollback();
                return $this->err(401, 'Key has expired');
            }
        }

        if (!$this->checkLimitsAtomic($key, $credits)) {
            $db->transRollback();
            return $this->err(429, 'Rate limit exceeded');
        }

        $this->usageModel->increment($key->id, $credits);

        $db->query('UPDATE keys_keys SET last_used_at = UTC_TIMESTAMP() WHERE id = ?', [$key->id]);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->err(500, 'Transaction failed');
        }

        return $key;
    }

    private function err(int $status, string $message): object
    {
        return (object)['error' => true, 'status' => $status, 'message' => $message];
    }

    private function checkLimitsAtomic(object $key, int $credits = 0): bool
    {
        $limitsRequests = json_decode($key->limits_requests, true) ?? [];
        $limitsCredits  = json_decode($key->limits_credits, true) ?? [];

        foreach ($limitsRequests as $limit) {
            $sum = $this->usageModel->sumForKey($key->id, (int)$limit['days']);
            if (($sum->requests + 1) > (int)$limit['max']) {
                return false;
            }
        }

        foreach ($limitsCredits as $limit) {
            $sum = $this->usageModel->sumForKey($key->id, (int)$limit['days']);
            if (($sum->credits + $credits) > (int)$limit['max']) {
                return false;
            }
        }

        return true;
    }
}
