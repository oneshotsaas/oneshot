<?php

namespace OneShot\Notifications\Channels;

use OneShot\Core\Models\Task;
use OneShot\Core\Services\TaskRunner;

class Telegram
{
    public function send(object $user, string $title, array $data = []): void
    {
        if (!($user->telegram_id ?? null)) return;

        $text = '<b>' . esc($title) . '</b>';
        if (!empty($data['body'])) {
            $text .= "\n" . esc($data['body']);
        }

        $taskId = (new Task())->add([
            'type'    => 'notification.telegram',
            'payload' => json_encode([
                'chat_id' => $user->telegram_id,
                'text'    => $text,
            ]),
        ]);

        if (!option('notifications.queue_mode')) {
            (new TaskRunner())->runOne($taskId);
        }
    }
}
