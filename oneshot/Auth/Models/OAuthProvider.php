<?php

namespace OneShot\Auth\Models;

use OneShot\Core\Models\Base;

class OAuthProvider extends Base
{
    protected $table         = 'auth_providers';
    protected $allowedFields = ['user_id', 'provider', 'provider_id', 'provider_email'];
    protected $useTimestamps  = true;
    protected $useSoftDeletes = false;

    public function findByProvider(string $provider, string $providerId): object|null
    {
        return $this->where('provider', $provider)
                    ->where('provider_id', $providerId)
                    ->limit(1)
                    ->first() ?: null;
    }
}
