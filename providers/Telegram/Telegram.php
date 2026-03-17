<?php

namespace Providers\Telegram;

use OneShot\Core\Contracts\Notify;

class Telegram implements Notify
{
    public function __construct(
        private string $botToken = '',
        private string $chatId   = ''
    ) {
        $this->botToken = $botToken ?: env('TELEGRAM_BOT_TOKEN', '');
        $this->chatId   = $chatId   ?: env('TELEGRAM_CHAT_ID', '');
    }

    public function send(string $to, string $message, array $options = []): bool
    {
        $chatId = $to ?: $this->chatId;
        $url    = "https://api.telegram.org/bot{$this->botToken}/sendMessage";

        $response = file_get_contents($url . '?' . http_build_query([
            'chat_id' => $chatId,
            'text'    => $message,
            'parse_mode' => $options['parse_mode'] ?? 'HTML',
        ]));

        return $response !== false;
    }
}
