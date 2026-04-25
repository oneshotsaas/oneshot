<?php

namespace Providers\Email;

use OneShot\Core\Contracts\Notify;

class Email implements Notify
{
    public function send(string|object $to, string $message, array $options = []): bool
    {
        $to = is_object($to) ? ($to->email ?? '') : $to;
        if (!$to) return false;

        $subject = $options['subject'] ?? 'Notification';

        return (new \OneShot\Auth\Services\MailService())->send($to, $subject, $message);
    }
}
