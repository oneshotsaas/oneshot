<?php

namespace OneShot\Billing\Controllers\Admin;

use OneShot\Billing\Models\Promotion;

class Promotions extends Billing
{
    private Promotion $model;

    public function initController($request, $response, $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->model = new Promotion();
        $this->appendBC(__('billing.promotions', 'Promotions'), route_to('admin.billing.promotions'));
    }

    public function index(): string
    {
        $this->share('page_actions_view', 'Billing::admin/promotions/_actions');
        return $this->render('Billing::admin/promotions/index', [
            'promotions' => $this->model->where('deleted_at IS NULL')->orderBy('id', 'DESC')->findAll(),
        ]);
    }

    public function create(): string
    {
        $this->appendBC(__('billing.new', 'New'));
        $this->share('page_actions_view', 'Billing::admin/promotions/_actions_form');
        return $this->render('Billing::admin/promotions/form', ['promo' => null]);
    }

    public function store(): \CodeIgniter\HTTP\RedirectResponse
    {
        $data         = $this->request->getPost(['code','description','discount_type','discount_value','applies_to','max_uses','valid_from','valid_until','is_active']);
        $data['code'] = strtoupper(trim($data['code']));
        $data['max_uses']   = !empty($data['max_uses'])   ? (int)$data['max_uses']   : null;
        $data['valid_from'] = !empty($data['valid_from']) ? $data['valid_from'] : null;
        $data['valid_until']= !empty($data['valid_until'])? $data['valid_until'] : null;
        $this->model->add($data);
        return $this->redirectWith(route_to('admin.billing.promotions'), __('billing.saved', 'Saved'));
    }

    public function edit(string $hash): string
    {
        $promo = $this->model->getById(signedId($hash));
        if (!$promo) {
            return $this->redirectWith(route_to('admin.billing.promotions'), __('billing.not_found', 'Not found'), 'error');
        }
        $this->appendBC($promo->code);
        $this->share('page_actions_view', 'Billing::admin/promotions/_actions_form');
        return $this->render('Billing::admin/promotions/form', compact('promo'));
    }

    public function update(string $hash): \CodeIgniter\HTTP\RedirectResponse
    {
        $id   = signedId($hash);
        $data = $this->request->getPost(['code','description','discount_type','discount_value','applies_to','max_uses','valid_from','valid_until','is_active']);
        $data['code']       = strtoupper(trim($data['code']));
        $data['max_uses']   = !empty($data['max_uses'])   ? (int)$data['max_uses']   : null;
        $data['valid_from'] = !empty($data['valid_from']) ? $data['valid_from'] : null;
        $data['valid_until']= !empty($data['valid_until'])? $data['valid_until'] : null;
        $this->model->save(array_merge($data, ['id' => $id]));
        return $this->redirectWith(route_to('admin.billing.promotions'), __('billing.saved', 'Saved'));
    }

    public function destroy(string $hash): \CodeIgniter\HTTP\RedirectResponse
    {
        $this->model->delete(signedId($hash));
        return $this->redirectWith(route_to('admin.billing.promotions'), __('billing.deleted', 'Deleted'));
    }
}
