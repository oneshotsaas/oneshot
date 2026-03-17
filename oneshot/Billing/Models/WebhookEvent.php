<?php

namespace OneShot\Billing\Models;

use OneShot\Core\Models\Base;

class WebhookEvent extends Base
{
    protected $table          = 'billing_webhook_events';
    protected $useSoftDeletes = false;
    protected $useTimestamps  = false;
    protected $allowedFields  = [
        'provider', 'event_id', 'event_type', 'status',
        'processing_token', 'processing_started_at', 'processed_at', 'payload_hash',
    ];
}
