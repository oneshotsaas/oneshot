<?php

namespace OneShot\Core\Models;

class Event extends Base
{
    protected $table          = 'events';
    protected $allowedFields  = ['uuid', 'name', 'payload'];
    protected $useSoftDeletes = false;
    protected $useTimestamps  = true;
    protected $updatedField   = '';
    protected $deletedField   = '';
}
