<div class="max-w-lg grid gap-4">
    <div class="card bg-base-200 shadow">
        <div class="card-body gap-3">
            <?php $statusColors = ['paid'=>'badge-success','open'=>'badge-warning','draft'=>'badge-ghost','void'=>'badge-error','uncollectible'=>'badge-error'] ?>
            <div class="flex items-center justify-between">
                <h3 class="font-bold"><?= __('billing.invoice_id', '#') ?><?= (int) $invoice->id ?></h3>
                <span class="badge <?= $statusColors[$invoice->status] ?? 'badge-ghost' ?>"><?= esc($invoice->status) ?></span>
            </div>
            <div class="grid grid-cols-2 gap-3 text-sm">
                <div>
                    <span class="opacity-60"><?= __('billing.subscriber', 'User') ?></span>
                    <p><?= (int) $invoice->user_id ?></p>
                </div>
                <div>
                    <span class="opacity-60"><?= __('billing.amount', 'Amount') ?></span>
                    <p class="font-bold">$<?= number_format($invoice->amount / 100, 2) ?> <?= strtoupper($invoice->currency) ?></p>
                </div>
                <?php if ($invoice->amount_discount): ?>
                <div>
                    <span class="opacity-60"><?= __('billing.discount', 'Discount') ?></span>
                    <p>-$<?= number_format($invoice->amount_discount / 100, 2) ?></p>
                </div>
                <?php endif ?>
                <?php if ($invoice->tax_amount): ?>
                <div>
                    <span class="opacity-60"><?= __('billing.tax_amount', 'Tax') ?></span>
                    <p>$<?= number_format($invoice->tax_amount / 100, 2) ?> (<?= $invoice->tax_rate ?>%)</p>
                </div>
                <?php endif ?>
                <div>
                    <span class="opacity-60"><?= __('billing.created_at', 'Created') ?></span>
                    <p><?= esc($invoice->created_at) ?></p>
                </div>
                <?php if ($invoice->paid_at): ?>
                <div>
                    <span class="opacity-60"><?= __('billing.paid_at', 'Paid At') ?></span>
                    <p><?= esc($invoice->paid_at) ?></p>
                </div>
                <?php endif ?>
                <?php if ($invoice->retry_count): ?>
                <div>
                    <span class="opacity-60">Retries</span>
                    <p><?= (int) $invoice->retry_count ?><?= $invoice->next_retry_at ? ' (next: ' . esc($invoice->next_retry_at) . ')' : '' ?></p>
                </div>
                <?php endif ?>
            </div>

            <?php if (!empty($data)): ?>
            <div class="divider my-1"></div>
            <div class="text-sm">
                <p class="opacity-60 mb-1"><?= __('billing.details', 'Details') ?></p>
                <?php foreach ($data as $k => $v): ?>
                <div class="flex gap-2">
                    <span class="opacity-60 font-mono text-xs"><?= esc($k) ?></span>
                    <span><?= esc($v) ?></span>
                </div>
                <?php endforeach ?>
            </div>
            <?php endif ?>
        </div>
    </div>
</div>
