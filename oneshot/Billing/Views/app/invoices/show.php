<div class="max-w-md">
    <div class="card bg-base-200 shadow">
        <div class="card-body gap-3">
            <?php $statusColors = ['paid'=>'badge-success','open'=>'badge-warning','draft'=>'badge-ghost','void'=>'badge-error','uncollectible'=>'badge-error'] ?>
            <div class="flex items-center justify-between">
                <h3 class="font-bold text-lg"><?= __('billing.invoice_id', '#') ?><?= (int) $invoice->id ?></h3>
                <span class="badge <?= $statusColors[$invoice->status] ?? 'badge-ghost' ?>"><?= esc($invoice->status) ?></span>
            </div>

            <div class="text-sm space-y-2">
                <?php if (!empty($data['plan_name'])): ?>
                <div class="flex justify-between">
                    <span class="opacity-60"><?= __('billing.plan', 'Plan') ?></span>
                    <span><?= esc($data['plan_name']) ?> / <?= esc($data['interval'] ?? '') ?></span>
                </div>
                <?php endif ?>
                <?php if (!empty($data['package_credits'])): ?>
                <div class="flex justify-between">
                    <span class="opacity-60"><?= __('billing.credits', 'Credits') ?></span>
                    <span><?= number_format($data['package_credits']) ?></span>
                </div>
                <?php endif ?>
                <div class="flex justify-between">
                    <span class="opacity-60"><?= __('billing.subtotal', 'Subtotal') ?></span>
                    <span>$<?= number_format(($invoice->amount + $invoice->amount_discount) / 100, 2) ?></span>
                </div>
                <?php if ($invoice->amount_discount): ?>
                <div class="flex justify-between text-success">
                    <span class="opacity-60"><?= __('billing.discount', 'Discount') ?></span>
                    <span>-$<?= number_format($invoice->amount_discount / 100, 2) ?></span>
                </div>
                <?php endif ?>
                <?php if ($invoice->tax_amount): ?>
                <div class="flex justify-between">
                    <span class="opacity-60"><?= __('billing.tax_amount', 'Tax') ?> (<?= $invoice->tax_rate ?>%)</span>
                    <span>$<?= number_format($invoice->tax_amount / 100, 2) ?></span>
                </div>
                <?php endif ?>
                <div class="flex justify-between font-bold border-t pt-2 mt-1">
                    <span><?= __('billing.total', 'Total') ?></span>
                    <span>$<?= number_format($invoice->amount / 100, 2) ?> <?= strtoupper($invoice->currency) ?></span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="opacity-60"><?= __('billing.date', 'Date') ?></span>
                    <span><?= esc(substr($invoice->created_at, 0, 10)) ?></span>
                </div>
                <?php if ($invoice->paid_at): ?>
                <div class="flex justify-between text-sm">
                    <span class="opacity-60"><?= __('billing.paid_at', 'Paid At') ?></span>
                    <span><?= esc(substr($invoice->paid_at, 0, 10)) ?></span>
                </div>
                <?php endif ?>
            </div>
        </div>
    </div>
</div>
