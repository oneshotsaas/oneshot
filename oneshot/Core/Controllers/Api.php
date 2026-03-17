<?php

namespace OneShot\Core\Controllers;

use CodeIgniter\HTTP\ResponseInterface;

abstract class Api extends Base
{
    protected array $public = [];

    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ): void {
        parent::initController($request, $response, $logger);

        $method = $this->router->methodName();
        if (! in_array($method, $this->public, true)) {
            $this->checkToken();
        }
    }

    protected function checkToken(): void
    {
        // Override in modules for custom token validation
    }

    protected function ok(mixed $data = null, int $code = 200): ResponseInterface
    {
        return $this->response
            ->setStatusCode($code)
            ->setJSON(['success' => true, 'data' => $data]);
    }

    protected function fail(string $msg, int $code = 400, mixed $errors = null): ResponseInterface
    {
        $body = ['success' => false, 'message' => $msg];
        if ($errors !== null) {
            $body['errors'] = $errors;
        }

        return $this->response
            ->setStatusCode($code)
            ->setJSON($body);
    }
}
