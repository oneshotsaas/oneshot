<?php

namespace OneShot\Billing\Controllers\Admin;

use OneShot\Billing\Models\Cost;

class Costs extends Billing
{
    private Cost $model;

    public function initController($request, $response, $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->model = new Cost();
        $this->appendBC(__('billing.action_costs', 'Action Costs'), route_to('admin.billing.costs'));
    }

    public function index(): string
    {
        $this->share('page_actions_view', 'Billing::admin/costs/_actions');
        return $this->render('Billing::admin/costs/index', [
            'costs' => $this->model->orderBy('action')->findAll(),
        ]);
    }

    public function create(): string
    {
        $this->appendBC(__('billing.new', 'New'));
        $this->share('page_actions_view', 'Billing::admin/costs/_actions_form');
        return $this->render('Billing::admin/costs/form', ['cost' => null]);
    }

    public function store(): \CodeIgniter\HTTP\RedirectResponse
    {
        $post = $this->request->getPost();
        $err  = $this->model->validateMeta($post['unit_type'] ?? '', $post['meta'] ?? null);
        if ($err) {
            return $this->redirectWith(route_to('admin.billing.costs.create'), $err, 'error');
        }
        $this->model->add([
            'action'        => $post['action'],
            'label'         => $post['label'],
            'unit_type'     => $post['unit_type'],
            'cost_per_unit' => (float) $post['cost_per_unit'],
            'meta'          => !empty($post['meta']) ? $post['meta'] : null,
            'is_active'     => (int) ($post['is_active'] ?? 1),
        ]);
        return $this->redirectWith(route_to('admin.billing.costs'), __('billing.saved', 'Saved'));
    }

    public function edit(string $hash): string
    {
        $cost = $this->model->getById(signedId($hash));
        if (!$cost) {
            return $this->redirectWith(route_to('admin.billing.costs'), __('billing.not_found', 'Not found'), 'error');
        }
        $this->appendBC($cost->action);
        $this->share('page_actions_view', 'Billing::admin/costs/_actions_form');
        return $this->render('Billing::admin/costs/form', compact('cost'));
    }

    public function update(string $hash): \CodeIgniter\HTTP\RedirectResponse
    {
        $id   = signedId($hash);
        $post = $this->request->getPost();
        $err  = $this->model->validateMeta($post['unit_type'] ?? '', $post['meta'] ?? null);
        if ($err) {
            return $this->redirectWith(route_to('admin.billing.costs.edit', $hash), $err, 'error');
        }
        $this->model->save([
            'id'            => $id,
            'action'        => $post['action'],
            'label'         => $post['label'],
            'unit_type'     => $post['unit_type'],
            'cost_per_unit' => (float) $post['cost_per_unit'],
            'meta'          => !empty($post['meta']) ? $post['meta'] : null,
            'meta_version'  => (int) ($post['meta_version'] ?? 1),
            'is_active'     => (int) ($post['is_active'] ?? 1),
        ]);
        return $this->redirectWith(route_to('admin.billing.costs'), __('billing.saved', 'Saved'));
    }

    public function destroy(string $hash): \CodeIgniter\HTTP\RedirectResponse
    {
        $this->model->delete(signedId($hash));
        return $this->redirectWith(route_to('admin.billing.costs'), __('billing.deleted', 'Deleted'));
    }
}
