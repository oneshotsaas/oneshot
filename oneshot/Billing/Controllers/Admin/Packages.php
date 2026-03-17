<?php

namespace OneShot\Billing\Controllers\Admin;

use OneShot\Billing\Models\Package;

class Packages extends Billing
{
    private Package $model;

    public function initController($request, $response, $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->model = new Package();
        $this->appendBC(__('billing.packages', 'Packages'), route_to('admin.billing.packages'));
    }

    public function index(): string
    {
        $packages = $this->model->where('deleted_at IS NULL')->orderBy('sort')->findAll();
        foreach ($packages as $p) {
            $p->price_ui     = $this->centsToUi($p->price);
            $p->old_price_ui = $p->old_price !== null ? $this->centsToUi($p->old_price) : '';
        }
        $this->share('page_actions_view', 'Billing::admin/packages/_actions');
        return $this->render('Billing::admin/packages/index', compact('packages'));
    }

    public function create(): string
    {
        $this->appendBC(__('billing.new', 'New'));
        $this->share('page_actions_view', 'Billing::admin/packages/_actions_form');
        return $this->render('Billing::admin/packages/form', ['package' => null]);
    }

    public function store(): \CodeIgniter\HTTP\RedirectResponse
    {
        $post = $this->request->getPost();
        $this->model->add([
            'name'      => $post['name'],
            'credits'   => (int) $post['credits'],
            'price'     => $this->uiToCents($post['price'] ?? '0'),
            'old_price' => !empty($post['old_price']) ? $this->uiToCents($post['old_price']) : null,
            'badge'     => !empty($post['badge']) ? $post['badge'] : null,
            'currency'  => $post['currency'] ?? 'usd',
            'is_active' => (int) ($post['is_active'] ?? 0),
            'sort'      => (int) ($post['sort'] ?? 0),
        ]);
        return $this->redirectWith(route_to('admin.billing.packages'), __('billing.saved', 'Saved'));
    }

    public function edit(string $hash): string
    {
        $package = $this->model->getById(signedId($hash));
        if (!$package) {
            return $this->redirectWith(route_to('admin.billing.packages'), __('billing.not_found', 'Not found'), 'error');
        }
        $package->price_ui     = $this->centsToUi($package->price);
        $package->old_price_ui = $package->old_price !== null ? $this->centsToUi($package->old_price) : '';
        $this->appendBC($package->name);
        $this->share('page_actions_view', 'Billing::admin/packages/_actions_form');
        return $this->render('Billing::admin/packages/form', compact('package'));
    }

    public function update(string $hash): \CodeIgniter\HTTP\RedirectResponse
    {
        $id   = signedId($hash);
        $post = $this->request->getPost();
        $this->model->save([
            'id'        => $id,
            'name'      => $post['name'],
            'credits'   => (int) $post['credits'],
            'price'     => $this->uiToCents($post['price'] ?? '0'),
            'old_price' => !empty($post['old_price']) ? $this->uiToCents($post['old_price']) : null,
            'badge'     => !empty($post['badge']) ? $post['badge'] : null,
            'currency'  => $post['currency'] ?? 'usd',
            'is_active' => (int) ($post['is_active'] ?? 0),
            'sort'      => (int) ($post['sort'] ?? 0),
        ]);
        return $this->redirectWith(route_to('admin.billing.packages'), __('billing.saved', 'Saved'));
    }

    public function destroy(string $hash): \CodeIgniter\HTTP\RedirectResponse
    {
        $this->model->delete(signedId($hash));
        return $this->redirectWith(route_to('admin.billing.packages'), __('billing.deleted', 'Deleted'));
    }
}
