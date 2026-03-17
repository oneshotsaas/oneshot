<?php

namespace OneShot\Billing\Controllers\App;

use OneShot\Billing\Models\{Plan, PlanPrice, Invoice};

class Dashboard extends Billing
{
    public function index(): string
    {
        $userId = session()->get('user_id');
        $sub    = $this->billing->getSubscription($userId);
        $plan   = null;
        $price  = null;

        if ($sub) {
            $plan  = (new Plan())->getById($sub->plan_id);
            $price = (new PlanPrice())->getById($sub->plan_price_id);
        }

        $recentInvoices = (new Invoice())->where('user_id', $userId)
                                         ->where('deleted_at IS NULL')
                                         ->orderBy('id', 'DESC')
                                         ->limit(5)->findAll();

        return $this->render('Billing::app/dashboard/index', [
            'sub'            => $sub,
            'plan'           => $plan,
            'price'          => $price,
            'balance'        => $this->billing->getBalance($userId),
            'recentInvoices' => $recentInvoices,
        ]);
    }
}
