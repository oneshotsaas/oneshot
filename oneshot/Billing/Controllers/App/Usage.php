<?php

namespace OneShot\Billing\Controllers\App;

use OneShot\Billing\Models\Transaction;

class Usage extends Billing
{
    public function index(): string
    {
        $this->appendBC(__('billing.usage', 'Usage'), route_to('billing.usage'));

        $userId  = session()->get('user_id');
        $filters = [
            'date_from'     => $this->request->getGet('date_from'),
            'date_to'       => $this->request->getGet('date_to'),
            'action_prefix' => $this->request->getGet('action_prefix'),
        ];

        $model        = new Transaction();
        $transactions = $model->getForUser($userId, $filters, 30);
        $pager        = $model->pager;

        $this->share('page_actions_view', 'Billing::app/usage/_filters');
        return $this->render('Billing::app/usage/index', compact('transactions', 'pager', 'filters'));
    }
}
