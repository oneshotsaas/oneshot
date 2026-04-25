<?php

namespace OneShot\Notifications\Models;

use OneShot\Core\Models\Base;

class Notification extends Base
{
    protected $table          = 'notifications';
    protected $allowedFields  = ['user_id', 'type', 'title', 'url', 'data', 'read_at'];
    protected $useSoftDeletes = false;
    protected $useTimestamps  = true;
    protected $updatedField   = '';
    protected $deletedField   = '';

    public function getUnread(int $userId, int $limit = 10): array
    {
        return $this->where('user_id', $userId)
                    ->where('read_at IS NULL')
                    ->orderBy('created_at', 'DESC')
                    ->limit($limit)
                    ->findAll();
    }

    public function countUnread(int $userId): int
    {
        return $this->where('user_id', $userId)->where('read_at IS NULL')->countAllResults();
    }
}
