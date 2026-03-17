<?php

namespace OneShot\Billing\Controllers\App;

use OneShot\Core\Controllers\App;
use OneShot\Billing\Services\BillingService;

abstract class Billing extends App
{
    protected BillingService $billing;

    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ): void {
        parent::initController($request, $response, $logger);
        $this->billing = new BillingService();
        $this->appendBC(__('billing.billing', 'Billing & Credits'), route_to('billing.index'));
    }
}
