<?php

namespace OneShot\Billing\Models;

use OneShot\Core\Models\Base;

class ProviderRef extends Base
{
    protected $table          = 'billing_provider_refs';
    protected $useSoftDeletes = false;
    protected $allowedFields  = [
        'entity_type', 'entity_id', 'provider', 'ref_id', 'meta',
    ];

    public function findRef(string $entityType, int $entityId, string $provider): ?object
    {
        return $this->where('entity_type', $entityType)
                    ->where('entity_id', $entityId)
                    ->where('provider', $provider)
                    ->limit(1)->first();
    }

    public function findAllForEntity(string $entityType, int $entityId): array
    {
        return $this->where('entity_type', $entityType)
                    ->where('entity_id', $entityId)
                    ->findAll();
    }
}
