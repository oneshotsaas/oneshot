<?php

\OneShot\Core\Services\Dispatcher::listenAny(function (string $name, ?array $payload) {
    if (is_cli()) return; // no session/request in CLI

    $userId = session()->has('user_id') ? (int)session('user_id') : null;
    $ip     = clientIp();

    // Sanitize: keep only scalar values from payload
    $safe = $payload
        ? array_filter($payload, fn($v) => is_scalar($v) || is_null($v))
        : [];

    (new \OneShot\Activity\Services\Activity())->log($userId, $name, null, null, $safe, $ip);
});
