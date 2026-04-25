<?php

namespace OneShot\Core\Models;

class Task extends Base
{
    protected $table          = 'tasks';
    protected $allowedFields  = ['type', 'payload', 'status', 'attempts', 'max_attempts', 'error', 'retry_after', 'claimed_at', 'processed_at'];
    protected $useSoftDeletes = false;
    protected $useTimestamps  = true;
    protected $updatedField   = '';
    protected $deletedField   = '';
}
