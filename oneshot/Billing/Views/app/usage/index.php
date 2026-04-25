<?php
function billingFormatDetail(array $data): string {
    $parts = [];
    if (isset($data['model']))          $parts[] = $data['model'];
    if (isset($data['input_tokens']))   $parts[] = 'in: ' . number_format($data['input_tokens']);
    if (isset($data['output_tokens']))  $parts[] = 'out: ' . number_format($data['output_tokens']);
    if (isset($data['cached_tokens']) && $data['cached_tokens']) $parts[] = 'cached: ' . number_format($data['cached_tokens']);
    if (isset($data['size']))           $parts[] = $data['size'];
    if (isset($data['seconds']))        $parts[] = $data['seconds'] . 's';
    if (!empty($data['audio']))         $parts[] = 'with audio';
    if (isset($data['resolution']))     $parts[] = $data['resolution'];
    if (isset($data['provider']))       $parts[] = $data['provider'];
    return implode(' · ', $parts);
}

function billingActionBadge(string $action): string {
    $class = match(true) {
        $action === 'grant'           => 'badge-success',
        str_starts_with($action, 'llm.')   => 'badge-primary',
        str_starts_with($action, 'image.') => 'badge-info',
        str_starts_with($action, 'video.') => 'badge-warning',
        str_starts_with($action, 'deduct') => 'badge-error',
        default                       => 'badge-ghost',
    };
    return '<span class="badge badge-sm ' . $class . ' font-mono">' . esc($action) . '</span>';
}
?>
<div class="rounded-lg border border-base-300 overflow-x-auto overflow-hidden">
    <table class="table table-sm w-full min-w-max">
        <thead>
            <tr class="bg-base-200 text-xs uppercase tracking-wider opacity-70">
                <th class="font-semibold"><?= __('billing.date', 'Date') ?></th>
                <th class="font-semibold"><?= __('billing.action', 'Action') ?></th>
                <th class="font-semibold"><?= __('billing.details', 'Details') ?></th>
                <th class="font-semibold text-right"><?= __('billing.quantity', 'Qty') ?></th>
                <th class="font-semibold"><?= __('billing.unit_label', 'Unit') ?></th>
                <th class="font-semibold text-right"><?= __('billing.credits_used', 'Credits') ?></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-base-200">
            <?php foreach ($transactions as $tx): ?>
            <?php $detail = json_decode($tx->data ?? '{}', true) ?: []; ?>
            <tr class="hover:bg-base-200/50 transition-colors">
                <td class="whitespace-nowrap text-sm tabular-nums opacity-70">
                    <?= esc(substr($tx->created_at, 0, 16)) ?>
                </td>
                <td><?= billingActionBadge($tx->action ?? $tx->type) ?></td>
                <td class="text-xs opacity-60 max-w-xs truncate">
                    <?= esc(billingFormatDetail($detail)) ?: '—' ?>
                </td>
                <td class="text-right tabular-nums text-sm">
                    <?= $tx->quantity ? number_format($tx->quantity) : '<span class="opacity-30">—</span>' ?>
                </td>
                <td class="text-xs opacity-60">
                    <?= esc($tx->unit_type ?? '') ?: '<span class="opacity-30">—</span>' ?>
                </td>
                <td class="text-right tabular-nums font-mono font-semibold <?= $tx->amount >= 0 ? 'text-success' : 'text-error' ?>">
                    <?= $tx->amount >= 0 ? '+' : '' ?><?= number_format(abs((float)$tx->amount), 2) ?>
                </td>
            </tr>
            <?php endforeach ?>
            <?php if (empty($transactions)): ?>
            <tr>
                <td colspan="6" class="py-12 text-center text-sm opacity-40">
                    <?= __('billing.no_usage', 'No usage records found') ?>
                </td>
            </tr>
            <?php endif ?>
        </tbody>
    </table>
</div>

<?php if (!empty($pager)): ?>
<div class="mt-4">
    <?= $pager->links() ?>
</div>
<?php endif ?>
