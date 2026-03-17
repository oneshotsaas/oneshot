<?php

namespace OneShot\Billing\Controllers\App;

use OneShot\Billing\Models\Invoice as InvoiceModel;
use OneShot\Billing\Models\{Plan, Package};

class Invoice extends Billing
{
    public function index(): string
    {
        $this->appendBC(__('billing.invoices', 'Invoices'), route_to('billing.invoices'));

        $userId   = session()->get('user_id');
        $model    = new InvoiceModel();
        $invoices = $model->getForUser($userId);
        $pager    = $model->pager;

        return $this->render('Billing::app/invoices/index', compact('invoices', 'pager'));
    }

    public function show(string $hash): string
    {
        $id      = signedId($hash);
        $userId  = session()->get('user_id');
        $invoice = (new InvoiceModel())->where('id', $id)->where('user_id', $userId)->limit(1)->first();

        if (!$invoice) {
            return $this->redirectWith(route_to('billing.invoices'), __('billing.not_found', 'Not found'), 'error');
        }

        $this->appendBC(__('billing.invoices', 'Invoices'), route_to('billing.invoices'));
        $this->appendBC('#' . $id);

        $plan    = $invoice->subscription_id ? (new Plan())->getById(
            \Config\Database::connect()->table('billing_subscriptions')
                ->select('plan_id')->where('id', $invoice->subscription_id)->limit(1)->get()->getRowObject()?->plan_id ?? 0
        ) : null;
        $package = $invoice->package_id ? (new Package())->getById($invoice->package_id) : null;
        $data    = json_decode($invoice->data ?? '{}', true) ?: [];

        $this->share('page_actions_view', 'Billing::app/invoices/_actions_show');
        return $this->render('Billing::app/invoices/show', compact('invoice', 'plan', 'package', 'data'));
    }
}
