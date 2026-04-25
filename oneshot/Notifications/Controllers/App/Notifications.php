<?php

namespace OneShot\Notifications\Controllers\App;

use OneShot\Core\Controllers\App;
use OneShot\Notifications\Services\Notifier;

abstract class Notifications extends App
{
    protected Notifier $notifier;

    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ): void {
        parent::initController($request, $response, $logger);
        $this->notifier = new Notifier();
    }
}
