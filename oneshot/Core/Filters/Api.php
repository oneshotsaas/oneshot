<?php

namespace OneShot\Core\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class Api implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null): mixed
    {
        return (new \OneShot\Keys\Filters\ApiKey())->before($request, $arguments);
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null): mixed
    {
        $response->setHeader('Content-Type', 'application/json');
        return null;
    }
}
