<?php

namespace OneShot\Activity\Models;

use OneShot\Core\Models\Base;

class Log extends Base
{
    protected $table          = 'activity_logs';
    protected $allowedFields  = ['user_id', 'action', 'subject_type', 'subject_id', 'metadata', 'ip'];
    protected $useSoftDeletes = false;
    protected $useTimestamps  = false;

    public function insertLog(array $data): void
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $this->db->table($this->table)->insert($data);
    }
}
