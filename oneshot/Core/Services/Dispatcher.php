<?php

namespace OneShot\Core\Services;

use CodeIgniter\Events\Events;
use OneShot\Core\Models\Event;

class Dispatcher
{
    private static array $anyListeners = [];

    // $payload must be array|null — non-array payloads are not supported
    public static function dispatch(string $name, ?array $payload = null): void
    {
        if (env('app.secretKey')) {
            try {
                $b    = random_bytes(16);
                $b[6] = chr(ord($b[6]) & 0x0f | 0x40); // version 4
                $b[8] = chr(ord($b[8]) & 0x3f | 0x80); // variant
                $uuid = vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($b), 4));

                // Sanitize: store only scalar values
                $safe = $payload ? array_filter($payload, fn($v) => is_scalar($v) || is_null($v)) : null;

                (new Event())->add([
                    'uuid'    => $uuid,
                    'name'    => $name,
                    'payload' => $safe ? json_encode($safe) : null,
                ]);
            } catch (\Throwable) {}
        }

        foreach (self::$anyListeners as $fn) {
            try { $fn($name, $payload); } catch (\Throwable) {}
        }

        Events::trigger($name, $payload ?? []);
    }

    public static function listen(string $event, callable $fn): void
    {
        Events::on($event, $fn);
    }

    public static function listenAny(callable $fn): void // fn(string $name, ?array $payload)
    {
        self::$anyListeners[] = $fn;
    }
}
