<?php

namespace OneShot\Billing\Controllers\Admin;

use OneShot\Core\Controllers\Admin;
use OneShot\Billing\Services\BillingService;

abstract class Billing extends Admin
{
    protected BillingService $billing;

    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ): void {
        parent::initController($request, $response, $logger);
        $this->billing = new BillingService();
        $this->appendBC(__('billing.billing', 'Billing'), route_to('admin.billing.plans'));
    }

    protected function centsToUi(int $cents): string
    {
        return number_format($cents / 100, 2, '.', '');
    }

    protected function uiToCents(string $val): int
    {
        return (int) round((float) $val * 100);
    }
}
