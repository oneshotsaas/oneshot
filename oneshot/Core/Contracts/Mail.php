<?php

namespace OneShot\Core\Contracts;

interface Mail
{
    public function send(string $to, string $subject, string $body, array $options = []): bool;
}
