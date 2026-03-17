<?php

namespace OneShot\Billing\Controllers\Admin;

use OneShot\Billing\Models\{Subscription, Plan, PlanPrice};

class Subscriptions extends Billing
{
    public function initController($request, $response, $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->appendBC(__('billing.subscriptions', 'Subscriptions'), route_to('admin.billing.subscriptions'));
    }

    public function index(): string
    {
        $model  = new Subscription();
        $subs   = $model->where('deleted_at IS NULL')->orderBy('id', 'DESC')->paginate(30);
        $pager  = $model->pager;

        $planIds = array_unique(array_column($subs, 'plan_id'));
        $plans   = [];
        if ($planIds) {
            foreach ((new Plan())->whereIn('id', $planIds)->findAll() as $p) {
                $plans[$p->id] = $p;
            }
        }

        return $this->render('Billing::admin/subscriptions/index', compact('subs', 'pager', 'plans'));
    }

    public function show(string $hash): string
    {
        $sub  = (new Subscription())->getById(signedId($hash));
        if (!$sub) {
            return $this->redirectWith(route_to('admin.billing.subscriptions'), __('billing.not_found', 'Not found'), 'error');
        }
        $plan  = (new Plan())->getById($sub->plan_id);
        $price = (new PlanPrice())->getById($sub->plan_price_id);
        $this->appendBC('#' . $sub->id);
        return $this->render('Billing::admin/subscriptions/show', compact('sub', 'plan', 'price'));
    }
}
