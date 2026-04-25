<?php

namespace OneShot\Notifications\Channels;

use OneShot\Notifications\Models\Notification;

class InApp
{
    public function send(int $userId, string $type, string $title, string $url = '', array $data = []): void
    {
        (new Notification())->add([
            'user_id' => $userId,
            'type'    => $type,
            'title'   => $title,
            'url'     => $url ?: null,
            'data'    => $data ? json_encode($data) : null,
        ]);
    }
}
