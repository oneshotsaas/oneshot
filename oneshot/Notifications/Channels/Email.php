<?php

namespace OneShot\Notifications\Channels;

use OneShot\Core\Models\Task;
use OneShot\Core\Services\TaskRunner;

class Email
{
    public function send(object $user, string $title, array $data = []): void
    {
        if (!$user->email) return;

        $taskId = (new Task())->add([
            'type'    => 'notification.email',
            'payload' => json_encode([
                'to'    => $user->email,
                'title' => $title,
                'body'  => $data['body'] ?? '',
            ]),
        ]);

        if (!option('notifications.queue_mode')) {
            (new TaskRunner())->runOne($taskId);
        }
    }
}
