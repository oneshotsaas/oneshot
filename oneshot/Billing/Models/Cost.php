<?php

namespace OneShot\Billing\Models;

use OneShot\Core\Models\Base;

class Cost extends Base
{
    protected $table          = 'billing_costs';
    protected $useSoftDeletes = false;
    protected $allowedFields  = [
        'action', 'label', 'unit_type', 'cost_per_unit', 'meta', 'meta_version', 'is_active',
    ];

    public function findByAction(string $action): ?object
    {
        return $this->where('action', $action)->where('is_active', 1)->limit(1)->first();
    }

    /**
     * Validate meta JSON structure based on unit_type.
     * Returns error string or null if valid.
     */
    public function validateMeta(string $unitType, ?string $metaJson): ?string
    {
        if (empty($metaJson)) {
            return null;
        }

        $meta = json_decode($metaJson, true);
        if ($meta === null) {
            return 'Invalid JSON';
        }

        if ($unitType === 'token') {
            if (isset($meta['batch']) && !is_numeric($meta['batch'])) {
                return 'batch must be numeric';
            }
            if (isset($meta['output_multiplier']) && !is_numeric($meta['output_multiplier'])) {
                return 'output_multiplier must be numeric';
            }
            if (isset($meta['cached_discount']) && !is_numeric($meta['cached_discount'])) {
                return 'cached_discount must be numeric';
            }
        }

        if (in_array($unitType, ['unit', 'second']) && isset($meta['variants'])) {
            foreach ($meta['variants'] as $key => $variant) {
                if (!isset($variant['type']) || !in_array($variant['type'], ['multiply', 'fixed'])) {
                    return "variant '$key' must have type: multiply|fixed";
                }
                if (!isset($variant['value']) || !is_numeric($variant['value'])) {
                    return "variant '$key' value must be numeric";
                }
            }
        }

        return null;
    }
}
