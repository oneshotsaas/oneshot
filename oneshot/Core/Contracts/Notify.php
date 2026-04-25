<?php

namespace OneShot\Core\Contracts;

interface Notify
{
    public function send(string|object $to, string $message, array $options = []): bool;
}
