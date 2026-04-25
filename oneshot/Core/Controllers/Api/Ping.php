<?php

namespace OneShot\Core\Controllers\Api;

use CodeIgniter\HTTP\ResponseInterface;

class Ping extends \OneShot\Core\Controllers\Api
{
    protected array $public = ['index'];

    public function index(): ResponseInterface
    {
        return $this->ok([
            'pong' => true,
            'time' => (new \DateTimeImmutable('now', new \DateTimeZone('UTC')))->format('Y-m-d\TH:i:s\Z'),
        ]);
    }
}
