<?php

namespace OneShot\Keys\Controllers\Api;

use CodeIgniter\HTTP\ResponseInterface;
use OneShot\Keys\Models\ApiKey;
use OneShot\Keys\Models\Usage;

class Keys extends \OneShot\Core\Controllers\Api
{
    public function me(): ResponseInterface
    {
        $keyId = (int)$_SERVER['KEYS_KEY_ID'];

        $key   = (new ApiKey())->find($keyId);
        $usage = (new Usage())->sumForKey($keyId, 0);

        $limitsRequests = json_decode($key->limits_requests, true) ?? [];
        $limitsCredits  = json_decode($key->limits_credits,  true) ?? [];

        return $this->ok([
            'name'            => $key->name,
            'status'          => $key->status,
            'expires_at'      => $key->expires_at,
            'limits_requests' => $limitsRequests,
            'limits_credits'  => $limitsCredits,
            'usage'           => [
                'requests' => (int)$usage->requests,
                'credits'  => (int)$usage->credits,
            ],
        ]);
    }
}
