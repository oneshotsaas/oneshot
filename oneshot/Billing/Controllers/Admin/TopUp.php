<?php

namespace OneShot\Billing\Controllers\Admin;

class TopUp extends Billing
{
    public function initController($request, $response, $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->appendBC(__('billing.topup', 'Top Up'), route_to('admin.billing.topup'));
    }

    public function form(string $hash): string
    {
        $userId = signedId($hash);
        $this->appendBC(__('billing.topup', 'Top Up'));
        $this->share('page_actions_view', 'Billing::admin/topup/_actions_form');
        return $this->render('Billing::admin/topup/form', [
            'userId'  => $userId,
            'hash'    => $hash,
            'balance' => $this->billing->getBalance($userId),
        ]);
    }

    public function store(string $hash): \CodeIgniter\HTTP\RedirectResponse
    {
        $userId  = signedId($hash);
        $credits = (float) $this->request->getPost('credits');
        $note    = $this->request->getPost('description');
        $adminId = session()->get('user_id');

        if ($credits == 0) {
            return $this->redirectWith(route_to('admin.billing.topup', $hash), __('billing.invalid_amount', 'Invalid amount'), 'error');
        }

        $type = $credits > 0 ? 'manual' : 'deduction';
        $this->billing->add($userId, $credits, $type, [
            'description' => $note ?: __('billing.admin_topup', 'Admin top-up'),
            'actor_id'    => $adminId,
            'actor_type'  => 'admin',
        ]);

        return $this->redirectWith(route_to('admin.billing.topup', $hash), __('billing.topup_done', 'Credits updated'));
    }
}
