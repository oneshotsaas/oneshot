<?php

namespace OneShot\Auth\Models;

use OneShot\Core\Models\Base;

class Token extends Base
{
    protected $table         = 'auth_tokens';
    protected $allowedFields = ['user_id', 'type', 'token', 'payload', 'expires_at', 'used_at', 'created_at'];
    protected $useTimestamps  = false;
    protected $useSoftDeletes = false;

    private const TTL = [
        'verify_email'     => 86400,   // 24 hours
        'reset_password'   => 3600,    // 1 hour
    ];

    /**
     * Create a new token. Returns the raw token (to embed in link/email).
     * Stores SHA-256 hash in DB. Lazy-cleans old tokens for this user+type.
     */
    public function create(int $userId, string $type, ?string $payload = null): string
    {
        // Lazy cleanup: remove expired/used tokens for this user+type
        $this->where('user_id', $userId)
             ->where('type', $type)
             ->groupStart()
                 ->where('expires_at <', date('Y-m-d H:i:s'))
                 ->orWhere('used_at IS NOT NULL', null, false)
             ->groupEnd()
             ->delete();

        // Soft limit: max 3 active tokens per user+type — delete oldest if exceeded
        $active = $this->where('user_id', $userId)
                       ->where('type', $type)
                       ->where('expires_at >', date('Y-m-d H:i:s'))
                       ->where('used_at IS NULL', null, false)
                       ->orderBy('created_at', 'ASC')
                       ->findAll();

        if (count($active) >= 3) {
            $this->delete($active[0]->id);
        }

        $raw    = bin2hex(random_bytes(32));
        $hash   = hash('sha256', $raw);
        $ttl    = self::TTL[$type] ?? 3600;
        $now    = date('Y-m-d H:i:s');
        $expiry = date('Y-m-d H:i:s', time() + $ttl);

        $this->insert([
            'user_id'    => $userId,
            'type'       => $type,
            'token'      => $hash,
            'payload'    => $payload,
            'expires_at' => $expiry,
            'created_at' => $now,
        ]);

        return $raw;
    }

    /**
     * Consume a raw token. Returns the token row or null.
     * Timing-safe: always performs hash_equals even if token not found.
     * Race-condition safe: uses atomic UPDATE with affected-rows check.
     */
    public function consume(string $rawToken, string $type): object|null
    {
        $inputHash = hash('sha256', $rawToken);
        $dummy     = str_repeat('a', 64);

        $stored = $this->where('token', $inputHash)
                       ->where('type', $type)
                       ->limit(1)
                       ->first();

        // Always compare — equalises timing for found vs not-found
        $match = hash_equals($stored->token ?? $dummy, $inputHash);

        if (! $match || $stored === null) {
            return null;
        }

        if ($stored->expires_at < date('Y-m-d H:i:s')) {
            return null;
        }

        if ($stored->used_at !== null) {
            return null;
        }

        // Atomic mark as used — race condition protection
        $affected = $this->db->affectedRows();
        $this->db->query(
            'UPDATE auth_tokens SET used_at = ? WHERE id = ? AND used_at IS NULL',
            [date('Y-m-d H:i:s'), $stored->id]
        );

        if ($this->db->affectedRows() === 0) {
            return null; // Another request consumed it first
        }

        return $stored;
    }

    /**
     * Get last created token of given type for a user (for cooldown checks).
     */
    public function lastCreated(int $userId, string $type): object|null
    {
        return $this->where('user_id', $userId)
                    ->where('type', $type)
                    ->orderBy('created_at', 'DESC')
                    ->limit(1)
                    ->first() ?: null;
    }

    /**
     * Invalidate all active tokens of a given type for a user.
     */
    public function invalidateAll(int $userId, string $type): void
    {
        $this->db->query(
            'UPDATE auth_tokens SET used_at = ? WHERE user_id = ? AND type = ? AND used_at IS NULL',
            [date('Y-m-d H:i:s'), $userId, $type]
        );
    }

    /**
     * Global cleanup — for spark auth:cleanup command.
     */
    public function cleanup(): int
    {
        $this->db->query(
            'DELETE FROM auth_tokens WHERE expires_at < ? OR used_at IS NOT NULL',
            [date('Y-m-d H:i:s')]
        );

        return $this->db->affectedRows();
    }
}
