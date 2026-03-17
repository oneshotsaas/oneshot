<?php

namespace Providers\Mailgun;

use OneShot\Core\Contracts\Mail;

class Mailgun implements Mail
{
    public function __construct(
        private string $apiKey = '',
        private string $domain = '',
        private string $from   = ''
    ) {
        $this->apiKey = $apiKey ?: env('MAILGUN_API_KEY', '');
        $this->domain = $domain ?: env('MAILGUN_DOMAIN', '');
        $this->from   = $from   ?: env('MAILGUN_FROM', '');
    }

    public function send(string $to, string $subject, string $body, array $options = []): bool
    {
        // TODO: implement Mailgun send via API
        return false;
    }
}
