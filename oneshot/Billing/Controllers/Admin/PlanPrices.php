<?php

namespace OneShot\Billing\Controllers\Admin;

use OneShot\Billing\Models\{Plan, PlanPrice};

class PlanPrices extends Billing
{
    private PlanPrice $model;

    public function initController($request, $response, $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->model = new PlanPrice();
    }

    public function index(string $planHash): string
    {
        $planId = signedId($planHash);
        $plan   = (new Plan())->getById($planId);
        if (!$plan) {
            return $this->redirectWith(route_to('admin.billing.plans'), __('billing.not_found', 'Not found'), 'error');
        }
        $prices = $this->model->getForPlan($planId);
        foreach ($prices as $p) {
            $p->price_ui       = $this->centsToUi($p->price);
            $p->old_price_ui   = $p->old_price !== null ? $this->centsToUi($p->old_price) : '';
            $p->promo_price_ui = $p->promo_price !== null ? $this->centsToUi($p->promo_price) : '';
            $p->hash           = signId($p->id);
            $p->update_url     = route_to('admin.billing.plan.price.update', $planHash, signId($p->id));
        }
        $this->appendBC(__('billing.plans', 'Plans'), route_to('admin.billing.plans'));
        $this->appendBC($plan->name, route_to('admin.billing.plan.edit', $planHash));
        $this->appendBC(__('billing.prices', 'Prices'));
        $this->share('page_actions_view', 'Billing::admin/plan_prices/_actions');
        return $this->render('Billing::admin/plan_prices/index', compact('plan', 'planHash', 'prices'));
    }

    public function store(string $planHash): \CodeIgniter\HTTP\RedirectResponse
    {
        $planId = signedId($planHash);
        $post   = $this->request->getPost();
        $data   = [
            'plan_id'           => $planId,
            'interval'          => $post['interval'],
            'price'             => $this->uiToCents($post['price'] ?? '0'),
            'old_price'         => !empty($post['old_price'])   ? $this->uiToCents($post['old_price'])   : null,
            'promo_price'       => !empty($post['promo_price']) ? $this->uiToCents($post['promo_price']) : null,
            'promo_ends_at'     => ($post['promo_ends_type'] ?? '') === 'date' && !empty($post['promo_ends_at']) ? $post['promo_ends_at'] : null,
            'promo_ends_days'   => ($post['promo_ends_type'] ?? '') === 'days' && !empty($post['promo_ends_days']) ? (int)$post['promo_ends_days'] : null,
            'currency'          => $post['currency'] ?? 'usd',
            'discount_pct'      => !empty($post['discount_pct']) ? (int)$post['discount_pct'] : null,
            'promo_discount_pct'=> !empty($post['promo_discount_pct']) ? (int)$post['promo_discount_pct'] : null,
        ];
        $this->model->add($data);
        return $this->redirectWith(route_to('admin.billing.plan.prices', $planHash), __('billing.saved', 'Saved'));
    }

    public function update(string $planHash, string $hash): \CodeIgniter\HTTP\RedirectResponse
    {
        $id   = signedId($hash);
        $post = $this->request->getPost();
        $data = [
            'id'                => $id,
            'price'             => $this->uiToCents($post['price'] ?? '0'),
            'old_price'         => !empty($post['old_price'])   ? $this->uiToCents($post['old_price'])   : null,
            'promo_price'       => !empty($post['promo_price']) ? $this->uiToCents($post['promo_price']) : null,
            'promo_ends_at'     => ($post['promo_ends_type'] ?? '') === 'date' && !empty($post['promo_ends_at']) ? $post['promo_ends_at'] : null,
            'promo_ends_days'   => ($post['promo_ends_type'] ?? '') === 'days' && !empty($post['promo_ends_days']) ? (int)$post['promo_ends_days'] : null,
            'currency'          => $post['currency'] ?? 'usd',
            'discount_pct'      => !empty($post['discount_pct']) ? (int)$post['discount_pct'] : null,
            'promo_discount_pct'=> !empty($post['promo_discount_pct']) ? (int)$post['promo_discount_pct'] : null,
        ];
        $this->model->save($data);
        return $this->redirectWith(route_to('admin.billing.plan.prices', $planHash), __('billing.saved', 'Saved'));
    }

    public function destroy(string $planHash, string $hash): \CodeIgniter\HTTP\RedirectResponse
    {
        $this->model->delete(signedId($hash));
        return $this->redirectWith(route_to('admin.billing.plan.prices', $planHash), __('billing.deleted', 'Deleted'));
    }
}
