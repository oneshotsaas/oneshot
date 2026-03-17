<?php

namespace OneShot\Auth\Controllers;

use OneShot\Core\Controllers\Front;
use OneShot\Auth\Services\Auth as AuthService;

class Auth extends Front
{
    private AuthService $auth;

    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ): void {
        parent::initController($request, $response, $logger);
        $this->auth = new AuthService();
    }
}
