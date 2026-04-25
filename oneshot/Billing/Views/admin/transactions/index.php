<div class="rounded-lg border border-base-300 overflow-x-auto overflow-hidden">
    <table class="table table-sm w-full min-w-max">
        <thead>
            <tr class="bg-base-200 text-xs uppercase tracking-wider opacity-70">
                <th class="font-semibold"><?= __('billing.date', 'Date') ?></th>
                <th class="font-semibold"><?= __('billing.subscriber', 'User') ?></th>
                <th class="font-semibold"><?= __('billing.type', 'Type') ?></th>
                <th class="font-semibold"><?= __('billing.action', 'Action') ?></th>
                <th class="font-semibold text-right"><?= __('billing.amount', 'Credits') ?></th>
                <th class="font-semibold text-right"><?= __('billing.balance_after', 'Balance After') ?></th>
                <th class="font-semibold"><?= __('billing.description', 'Description') ?></th>
                <th></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-base-200">
            <?php foreach ($transactions as $tx): ?>
            <tr class="hover:bg-base-200/50 transition-colors">
                <td class="whitespace-nowrap text-sm tabular-nums opacity-70"><?= esc(substr($tx->created_at, 0, 16)) ?></td>
                <td class="tabular-nums opacity-70"><?= (int) $tx->user_id ?></td>
                <td><span class="badge badge-ghost badge-sm"><?= esc($tx->type) ?></span></td>
                <td class="font-mono text-xs opacity-60"><?= esc($tx->action ?? '—') ?></td>
                <td class="text-right tabular-nums font-mono font-semibold <?= $tx->amount >= 0 ? 'text-success' : 'text-error' ?>">
                    <?= $tx->amount >= 0 ? '+' : '' ?><?= number_format((float)$tx->amount, 4) ?>
                </td>
                <td class="text-right tabular-nums font-mono opacity-60"><?= number_format((float)$tx->balance_after, 2) ?></td>
                <td class="text-sm opacity-60 max-w-xs truncate"><?= esc($tx->description ?? '') ?: '<span class="opacity-40">—</span>' ?></td>
                <td>
                    <?php if (($tx->ref_type ?? '') === 'invoice' && $tx->ref_id && (float)$tx->amount > 0): ?>
                    <div class="tooltip tooltip-left" data-tip="<?= __('billing.refund', 'Refund') ?>">
                        <a href="<?= route_to('admin.billing.transaction.refund', signId($tx->ref_id)) ?>" class="btn btn-ghost btn-xs btn-square">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 14 4 9 9 4"/><path d="M20 20v-7a4 4 0 0 0-4-4H4"/></svg>
                        </a>
                    </div>
                    <?php endif ?>
                </td>
            </tr>
            <?php endforeach ?>
            <?php if (empty($transactions)): ?>
            <tr><td colspan="8" class="py-12 text-center text-sm opacity-40"><?= __('billing.no_usage', 'No records found') ?></td></tr>
            <?php endif ?>
        </tbody>
    </table>
</div>
<?= $pager->links() ?>
