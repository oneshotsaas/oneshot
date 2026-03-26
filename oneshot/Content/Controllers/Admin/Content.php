<?php

namespace OneShot\Content\Controllers\Admin;

use OneShot\Core\Controllers\Admin;

abstract class Content extends Admin
{
    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ): void {
        parent::initController($request, $response, $logger);
        $this->appendBC(__('content.content', 'Content'), route_to('admin.content.items'));
    }
}
