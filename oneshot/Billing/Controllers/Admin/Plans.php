<?php

namespace OneShot\Billing\Controllers\Admin;

use OneShot\Billing\Models\{Plan, PlanPrice};

class Plans extends Billing
{
    private Plan $model;

    public function initController($request, $response, $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->model = new Plan();
    }

    public function index(): string
    {
        $this->appendBC(__('billing.plans', 'Plans'), route_to('admin.billing.plans'));
        $this->share('page_actions_view', 'Billing::admin/plans/_actions');
        return $this->render('Billing::admin/plans/index', [
            'plans' => $this->model->where('deleted_at IS NULL')->orderBy('sort')->findAll(),
        ]);
    }

    public function create(): string
    {
        $this->appendBC(__('billing.plans', 'Plans'), route_to('admin.billing.plans'));
        $this->appendBC(__('billing.new', 'New'));
        $this->share('page_actions_view', 'Billing::admin/plans/_actions_form');
        return $this->render('Billing::admin/plans/form', ['plan' => null, 'prices' => []]);
    }

    public function store(): \CodeIgniter\HTTP\RedirectResponse
    {
        $data = $this->request->getPost(['name','slug','description','credits_included','trial_days','trial_credits','features','badge','hide_price','is_active','sort']);
        $data['features']      = $data['features'] ?: null;
        $data['trial_credits'] = !empty($data['trial_credits']) ? (int)$data['trial_credits'] : null;
        $data['hide_price']    = empty($data['hide_price']) ? 0 : 1;
        $data['is_active']     = empty($data['is_active'])  ? 0 : 1;
        $id = $this->model->add($data);
        notify(session()->get('user_id'), 'billing.provider_sync_required', __('billing.notify_sync_required', 'Plan prices updated — verify payment provider setup'), site_url(config('Prefixes')->admin . '/billing/install'), []);
        return $this->redirectWith(route_to('admin.billing.plan.prices', signId($id)), __('billing.saved', 'Saved'));
    }

    public function edit(string $hash): string
    {
        $id   = signedId($hash);
        $plan = $this->model->getById($id);
        if (!$plan) {
            return $this->redirectWith(route_to('admin.billing.plans'), __('billing.not_found', 'Not found'), 'error');
        }
        $prices = (new PlanPrice())->getForPlan($id);
        // Convert cents to UI
        foreach ($prices as $p) {
            $p->price_ui      = $this->centsToUi($p->price);
            $p->old_price_ui  = $p->old_price  !== null ? $this->centsToUi($p->old_price)  : '';
            $p->promo_price_ui= $p->promo_price !== null ? $this->centsToUi($p->promo_price) : '';
        }
        $this->appendBC(__('billing.plans', 'Plans'), route_to('admin.billing.plans'));
        $this->appendBC($plan->name);
        $this->share('page_actions_view', 'Billing::admin/plans/_actions_form');
        return $this->render('Billing::admin/plans/form', compact('plan', 'prices'));
    }

    public function update(string $hash): \CodeIgniter\HTTP\RedirectResponse
    {
        $id   = signedId($hash);
        $data = $this->request->getPost(['name','slug','description','credits_included','trial_days','trial_credits','features','badge','hide_price','is_active','sort']);
        $data['features']      = $data['features'] ?: null;
        $data['trial_credits'] = !empty($data['trial_credits']) ? (int)$data['trial_credits'] : null;
        $data['hide_price']    = empty($data['hide_price']) ? 0 : 1;
        $data['is_active']     = empty($data['is_active'])  ? 0 : 1;
        $this->model->save(array_merge($data, ['id' => $id]));
        notify(session()->get('user_id'), 'billing.provider_sync_required', __('billing.notify_sync_required', 'Plan prices updated — verify payment provider setup'), site_url(config('Prefixes')->admin . '/billing/install'), []);
        return $this->redirectWith(route_to('admin.billing.plans'), __('billing.saved', 'Saved'));
    }

    public function destroy(string $hash): \CodeIgniter\HTTP\RedirectResponse
    {
        $this->model->delete(signedId($hash));
        notify(session()->get('user_id'), 'billing.provider_sync_required', __('billing.notify_sync_required', 'Plan prices updated — verify payment provider setup'), site_url(config('Prefixes')->admin . '/billing/install'), []);
        return $this->redirectWith(route_to('admin.billing.plans'), __('billing.deleted', 'Deleted'));
    }
}
