<?php

namespace Providers\Telegram;

use OneShot\Core\Contracts\Notify;

class Telegram implements Notify
{
    public function __construct(
        private string $botToken = '',
        private string $chatId   = ''
    ) {
        $this->botToken = $botToken ?: option('telegram.bot_token') ?: env('TELEGRAM_BOT_TOKEN', '');
        $this->chatId   = $chatId   ?: env('TELEGRAM_CHAT_ID', '');
    }

    public function send(string|object $to, string $message, array $options = []): bool
    {
        $chatId = is_object($to) ? ($to->telegram_id ?? '') : $to;
        $chatId = $chatId ?: $this->chatId;
        $url    = "https://api.telegram.org/bot{$this->botToken}/sendMessage";

        $response = file_get_contents($url . '?' . http_build_query([
            'chat_id' => $chatId,
            'text'    => $message,
            'parse_mode' => $options['parse_mode'] ?? 'HTML',
        ]));

        return $response !== false;
    }

    /**
     * Send a message to all admin chat IDs configured in settings (telegram.admin_chat_ids).
     * Returns true if at least one message was delivered successfully.
     */
    public function notifyAdmin(string $message, array $options = []): bool
    {
        $raw      = option('telegram.admin_chat_ids', '');
        $chatIds  = array_filter(array_map('trim', explode(',', $raw)));

        if (empty($chatIds)) {
            return false;
        }

        $ok = false;
        foreach ($chatIds as $chatId) {
            if ($this->send($chatId, $message, $options)) {
                $ok = true;
            }
        }

        return $ok;
    }
}
