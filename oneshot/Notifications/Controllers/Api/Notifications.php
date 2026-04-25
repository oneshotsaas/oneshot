<?php

namespace OneShot\Notifications\Controllers\Api;

use OneShot\Core\Controllers\Api;
use OneShot\Notifications\Services\Notifier;

class Notifications extends Api
{
    public function unread(): \CodeIgniter\HTTP\ResponseInterface
    {
        $userId  = (int)session('user_id');
        $notifer = new Notifier();
        return $this->ok([
            'count' => $notifer->countUnread($userId),
            'items' => $notifer->getUnread($userId, 10),
        ]);
    }
}
