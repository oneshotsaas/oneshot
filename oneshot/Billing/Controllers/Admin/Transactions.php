<?php

namespace OneShot\Billing\Controllers\Admin;

use OneShot\Billing\Models\Transaction;

class Transactions extends Billing
{
    public function initController($request, $response, $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->appendBC(__('billing.usage', 'Usage'), route_to('admin.billing.transactions'));
    }

    public function index(): string
    {
        $filters = [
            'date_from'     => $this->request->getGet('date_from'),
            'date_to'       => $this->request->getGet('date_to'),
            'action_prefix' => $this->request->getGet('action_prefix'),
            'user_id'       => (int) $this->request->getGet('user_id'),
        ];

        $model  = new Transaction();
        $builder = $model->orderBy('id', 'DESC');

        if ($filters['date_from']) {
            $builder->where('created_at >=', $filters['date_from']);
        }
        if ($filters['date_to']) {
            $builder->where('created_at <=', $filters['date_to'] . ' 23:59:59');
        }
        if ($filters['action_prefix']) {
            $builder->like('action', $filters['action_prefix'], 'after');
        }
        if ($filters['user_id']) {
            $builder->where('user_id', $filters['user_id']);
        }

        $transactions = $builder->paginate(50);
        $pager        = $model->pager;

        $this->share('page_actions_view', 'Billing::admin/transactions/_filters');
        return $this->render('Billing::admin/transactions/index', compact('transactions', 'pager', 'filters'));
    }
}
