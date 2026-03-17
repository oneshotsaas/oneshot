<div class="rounded-lg border border-base-300 overflow-hidden">
    <table class="table table-sm w-full">
        <thead>
            <tr class="bg-base-200 text-xs uppercase tracking-wider opacity-70">
                <th class="font-semibold"><?= __('billing.invoice_id', '#') ?></th>
                <th class="font-semibold"><?= __('billing.subscriber', 'User') ?></th>
                <th class="font-semibold text-right"><?= __('billing.amount', 'Amount') ?></th>
                <th class="font-semibold"><?= __('billing.status', 'Status') ?></th>
                <th class="font-semibold"><?= __('billing.paid_at', 'Paid At') ?></th>
                <th class="font-semibold"><?= __('billing.created_at', 'Created') ?></th>
                <th></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-base-200">
            <?php
            $statusColors = ['paid'=>'badge-success','open'=>'badge-warning','draft'=>'badge-ghost','void'=>'badge-error','uncollectible'=>'badge-error'];
            foreach ($invoices as $inv): ?>
            <tr class="hover:bg-base-200/50 transition-colors">
                <td class="tabular-nums opacity-50 text-xs">#<?= (int) $inv->id ?></td>
                <td class="opacity-70"><?= (int) $inv->user_id ?></td>
                <td class="text-right tabular-nums font-medium">$<?= number_format($inv->amount / 100, 2) ?> <span class="opacity-40 text-xs"><?= strtoupper($inv->currency) ?></span></td>
                <td><span class="badge badge-sm <?= $statusColors[$inv->status] ?? 'badge-ghost' ?>"><?= esc($inv->status) ?></span></td>
                <td class="text-sm opacity-60 whitespace-nowrap"><?= $inv->paid_at ? esc(substr($inv->paid_at, 0, 10)) : '<span class="opacity-40">—</span>' ?></td>
                <td class="text-sm opacity-60 whitespace-nowrap"><?= esc(substr($inv->created_at, 0, 10)) ?></td>
                <td>
                    <div class="flex items-center justify-end">
                        <div class="tooltip tooltip-left" data-tip="<?= __('billing.view', 'View') ?>">
                            <a href="<?= route_to('admin.billing.invoice', signId($inv->id)) ?>" class="btn btn-ghost btn-xs btn-square">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            </a>
                        </div>
                    </div>
                </td>
            </tr>
            <?php endforeach ?>
            <?php if (empty($invoices)): ?>
            <tr><td colspan="7" class="py-12 text-center text-sm opacity-40"><?= __('billing.no_invoices', 'No invoices found') ?></td></tr>
            <?php endif ?>
        </tbody>
    </table>
</div>
<?= $pager->links() ?>
