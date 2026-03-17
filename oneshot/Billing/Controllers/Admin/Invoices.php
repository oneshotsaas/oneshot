<?php

namespace OneShot\Billing\Controllers\Admin;

use OneShot\Billing\Models\Invoice;

class Invoices extends Billing
{
    public function initController($request, $response, $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->appendBC(__('billing.invoices', 'Invoices'), route_to('admin.billing.invoices'));
    }

    public function index(): string
    {
        $model    = new Invoice();
        $invoices = $model->where('deleted_at IS NULL')->orderBy('id', 'DESC')->paginate(50);
        $pager    = $model->pager;
        return $this->render('Billing::admin/invoices/index', compact('invoices', 'pager'));
    }

    public function show(string $hash): string
    {
        $invoice = (new Invoice())->getById(signedId($hash));
        if (!$invoice) {
            return $this->redirectWith(route_to('admin.billing.invoices'), __('billing.not_found', 'Not found'), 'error');
        }
        $data = json_decode($invoice->data ?? '{}', true) ?: [];
        $this->appendBC('#' . $invoice->id);
        $this->share('page_actions_view', 'Billing::admin/invoices/_actions_show');
        return $this->render('Billing::admin/invoices/show', compact('invoice', 'data'));
    }
}
