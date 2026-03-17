<?php

namespace OneShot\Core\Contracts;

interface Notify
{
    public function send(string $to, string $message, array $options = []): bool;
}
