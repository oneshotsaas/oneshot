<?php

namespace OneShot\Billing\Controllers\Admin;

use OneShot\Billing\Models\{Transaction, Invoice};

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

        $model   = new Transaction();
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
        $this->share('page_subbar_view', 'Billing::admin/transactions/_subbar');
        return $this->render('Billing::admin/transactions/index', compact('transactions', 'pager', 'filters'));
    }

    public function refundForm(string $hash): string
    {
        $invoiceId = signedId($hash);
        $invoice   = (new Invoice())->getById($invoiceId);
        if (!$invoice) {
            return $this->redirectWith(route_to('admin.billing.transactions'), __('billing.not_found', 'Not found'), 'error');
        }

        $db = \Config\Database::connect();

        // Credits granted for this invoice
        $creditsGranted = (float)$db->table('billing_transactions')
                                    ->selectSum('amount')
                                    ->where('ref_type', 'invoice')
                                    ->where('ref_id', $invoiceId)
                                    ->where('amount >', 0)
                                    ->get()->getRowObject()->amount;

        $currentBalance = $this->billing->getBalance((int)$invoice->user_id);

        $this->appendBC(__('billing.issue_refund', 'Issue Refund'));

        return $this->render('Billing::admin/transactions/refund', [
            'invoice'        => $invoice,
            'hash'           => $hash,
            'creditsGranted' => $creditsGranted,
            'currentBalance' => $currentBalance,
        ]);
    }

    public function refund(string $hash): \CodeIgniter\HTTP\RedirectResponse
    {
        $invoiceId     = signedId($hash);
        $invoice       = (new Invoice())->getById($invoiceId);
        if (!$invoice) {
            return $this->redirectWith(route_to('admin.billing.transactions'), __('billing.not_found', 'Not found'), 'error');
        }

        $refundAmount  = (int)round((float)($this->request->getPost('refund_amount') ?? 0) * 100);
        $creditsAction = $this->request->getPost('credits_action') ?? 'proportional';

        if ($refundAmount <= 0) {
            return $this->redirectWith(route_to('admin.billing.transaction.refund', $hash), __('billing.invalid_amount', 'Invalid amount'), 'error');
        }

        $db = \Config\Database::connect();

        // Find charge_id in invoice data
        $data     = json_decode($invoice->data ?? '{}', true);
        $chargeId = $data['charge_id'] ?? null;

        if (!$chargeId) {
            return $this->redirectWith(route_to('admin.billing.transaction.refund', $hash), __('billing.charge_id_missing', 'No charge ID found for this invoice'), 'error');
        }

        // Determine provider from subscription or invoice
        $provider = 'stripe';
        if ($invoice->subscription_id) {
            $sub = $db->table('billing_subscriptions')->where('id', $invoice->subscription_id)->limit(1)->get()->getRowObject();
            if ($sub && !empty($sub->provider)) {
                $provider = $sub->provider;
            }
        }

        try {
            service('oneTimePayment', $provider)->refund($chargeId, $refundAmount);
        } catch (\RuntimeException $e) {
            return $this->redirectWith(route_to('admin.billing.transaction.refund', $hash), $e->getMessage(), 'error');
        }

        // Mark invoice refunded
        $db->table('billing_invoices')->where('id', $invoiceId)->update([
            'status'     => 'refunded',
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        // Credits action
        $creditsGranted = (float)$db->table('billing_transactions')
                                    ->selectSum('amount')
                                    ->where('ref_type', 'invoice')
                                    ->where('ref_id', $invoiceId)
                                    ->where('amount >', 0)
                                    ->get()->getRowObject()->amount;

        $deduct = match($creditsAction) {
            'all'          => $creditsGranted,
            'none'         => 0,
            default        => ($invoice->amount > 0 && $creditsGranted > 0)
                             ? round($creditsGranted * ($refundAmount / $invoice->amount))
                             : 0,
        };

        if ($deduct > 0) {
            $this->billing->deduct((int)$invoice->user_id, $deduct, [
                'type'        => 'refund',
                'description' => 'Refund — invoice #' . $invoiceId,
                'ref_type'    => 'invoice',
                'ref_id'      => $invoiceId,
                'force'       => true,
            ]);
        }

        event('billing.refund_issued', ['user_id' => $invoice->user_id, 'invoice_id' => $invoiceId, 'amount' => $refundAmount]);
        notify((int)$invoice->user_id, 'billing.refund_issued', __('billing.notify_refund_issued', 'Refund issued'), site_url(route_to('billing.invoices')), []);

        return $this->redirectWith(route_to('admin.billing.transactions'), __('billing.refund_issued', 'Refund issued'));
    }
}
