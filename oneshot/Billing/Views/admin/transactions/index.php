<div class="rounded-lg border border-base-300 overflow-hidden">
    <table class="table table-sm w-full">
        <thead>
            <tr class="bg-base-200 text-xs uppercase tracking-wider opacity-70">
                <th class="font-semibold"><?= __('billing.date', 'Date') ?></th>
                <th class="font-semibold"><?= __('billing.subscriber', 'User') ?></th>
                <th class="font-semibold"><?= __('billing.type', 'Type') ?></th>
                <th class="font-semibold"><?= __('billing.action', 'Action') ?></th>
                <th class="font-semibold text-right"><?= __('billing.amount', 'Credits') ?></th>
                <th class="font-semibold text-right"><?= __('billing.balance_after', 'Balance After') ?></th>
                <th class="font-semibold"><?= __('billing.description', 'Description') ?></th>
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
            </tr>
            <?php endforeach ?>
            <?php if (empty($transactions)): ?>
            <tr><td colspan="7" class="py-12 text-center text-sm opacity-40"><?= __('billing.no_usage', 'No records found') ?></td></tr>
            <?php endif ?>
        </tbody>
    </table>
</div>
<?= $pager->links() ?>
