<?php

namespace OneShot\Billing\Controllers\App;

use OneShot\Billing\Models\{Plan, PlanPrice};

class Subscribe extends Billing
{
    public function checkout(string $hash): string
    {
        $planId = signedId($hash);
        $plan   = (new Plan())->getById($planId);
        if (!$plan || !$plan->is_active) {
            return $this->redirectWith(route_to('billing.plans'), __('billing.not_found', 'Not found'), 'error');
        }

        $priceModel      = new PlanPrice();
        $prices          = $priceModel->getForPlan($planId);
        $selectedInterval = $this->request->getGet('interval') ?? ($prices[0]->interval ?? 'month');
        $promoCode        = $this->request->getGet('promo_code') ?? '';

        $this->appendBC(__('billing.subscribe', 'Subscribe'));

        return $this->render('Billing::app/subscribe/checkout', [
            'plan'             => $plan,
            'prices'           => $prices,
            'selectedInterval' => $selectedInterval,
            'promoCode'        => $promoCode,
        ]);
    }

    public function store(string $hash): \CodeIgniter\HTTP\RedirectResponse
    {
        $planId   = signedId($hash);
        $interval = $this->request->getPost('interval');
        $plan     = (new Plan())->getById($planId);
        if (!$plan) {
            return $this->redirectWith(route_to('billing.plans'), __('billing.not_found', 'Not found'), 'error');
        }

        $userId    = session()->get('user_id');
        $promoCode = $this->request->getPost('promo_code');

        try {
            $this->billing->subscribe($userId, $planId, $interval, $promoCode ?: null);
            return $this->redirectWith(route_to('billing.subscribe.success'), __('billing.subscribed', 'Subscribed successfully'));
        } catch (\RuntimeException $e) {
            return $this->redirectWith(route_to('billing.subscribe', $hash), $e->getMessage(), 'error');
        }
    }

    public function success(): string
    {
        return $this->render('Billing::app/subscribe/success');
    }

    public function cancel(): \CodeIgniter\HTTP\RedirectResponse
    {
        $userId = session()->get('user_id');
        $this->billing->cancelSubscription($userId);
        return $this->redirectWith(route_to('billing.index'), __('billing.canceled', 'Subscription canceled'));
    }
}
