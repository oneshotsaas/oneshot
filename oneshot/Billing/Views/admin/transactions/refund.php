<div class="max-w-2xl"
     id="refund-root"
     data-invoice-amount="<?= (int)$invoice->amount ?>"
     data-credits-granted="<?= (float)$creditsGranted ?>"
     data-current-balance="<?= (float)$currentBalance ?>">
<form method="post" action="<?= route_to('admin.billing.transaction.refund', $hash) ?>" id="refund-form">
    <?= csrf_field() ?>
    <div class="card bg-base-200 shadow">
        <div class="card-body p-4 sm:p-6 gap-4">

            <div class="grid grid-cols-1 sm:grid-cols-[12rem_1fr] items-start gap-x-6 gap-y-1">
                <span class="text-sm opacity-60"><?= __('billing.invoice_id', 'Invoice') ?></span>
                <span class="text-sm font-mono">#<?= (int)$invoice->id ?></span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-[12rem_1fr] items-start gap-x-6 gap-y-1">
                <span class="text-sm opacity-60"><?= __('billing.invoice_amount', 'Invoice Amount') ?></span>
                <span class="text-sm tabular-nums font-semibold">$<?= number_format($invoice->amount / 100, 2) ?></span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-[12rem_1fr] items-start gap-x-6 gap-y-1">
                <span class="text-sm opacity-60"><?= __('billing.credits_granted', 'Credits Granted') ?></span>
                <span class="text-sm tabular-nums" id="info-credits-granted"><?= number_format($creditsGranted, 4) ?></span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-[12rem_1fr] items-start gap-x-6 gap-y-1">
                <span class="text-sm opacity-60"><?= __('billing.current_balance', 'Current Balance') ?></span>
                <span class="text-sm tabular-nums" id="info-current-balance"><?= number_format($currentBalance, 4) ?></span>
            </div>

            <div class="divider my-0"></div>

            <div class="grid grid-cols-1 sm:grid-cols-[12rem_1fr] items-start gap-x-6 gap-y-1">
                <div>
                    <span class="text-sm opacity-60"><?= __('billing.refund_amount', 'Refund Amount') ?> ($)</span>
                    <p class="text-xs opacity-40 mt-1"><?= __('billing.refund_amount_hint', 'Amount to refund in dollars. Must not exceed the original invoice amount.') ?></p>
                </div>
                <input type="number"
                    name="refund_amount"
                    id="refund_amount"
                    value="<?= number_format($invoice->amount / 100, 2, '.', '') ?>"
                    max="<?= number_format($invoice->amount / 100, 2, '.', '') ?>"
                    min="0.01"
                    step="0.01"
                    class="input input-sm input-bordered tabular-nums"
                    required>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-[12rem_1fr] items-start gap-x-6 gap-y-1">
                <div>
                    <span class="text-sm opacity-60"><?= __('billing.credits_action', 'Credits Action') ?></span>
                    <p class="text-xs opacity-40 mt-1"><?= __('billing.credits_action_hint', 'What to do with credits that were granted for this invoice.') ?></p>
                </div>
                <select name="credits_action" id="credits_action" class="select select-sm select-bordered">
                    <option value="proportional" selected><?= __('billing.credits_action_proportional', 'proportional — deduct pro-rata based on refund amount') ?></option>
                    <option value="all"><?= __('billing.credits_action_all', 'all — deduct all credits granted by this invoice') ?></option>
                    <option value="none"><?= __('billing.credits_action_none', 'none — keep credits, refund money only') ?></option>
                </select>
            </div>

            <!-- Live preview -->
            <div class="bg-base-300 rounded-lg p-3 text-sm grid gap-1" id="refund-preview">
                <div class="flex justify-between">
                    <span class="opacity-60"><?= __('billing.preview_credits_to_deduct', 'Credits to deduct') ?></span>
                    <span class="tabular-nums font-mono" id="preview-credits">—</span>
                </div>
                <div class="flex justify-between">
                    <span class="opacity-60"><?= __('billing.preview_balance_after', 'Balance after refund') ?></span>
                    <span class="tabular-nums font-mono" id="preview-balance">—</span>
                </div>
            </div>

            <div class="pt-2 flex gap-2">
                <button type="submit" class="btn btn-error btn-sm"><?= __('billing.issue_refund', 'Issue Refund') ?></button>
                <a href="<?= route_to('admin.billing.transactions') ?>" class="btn btn-ghost btn-sm"><?= __('billing.cancel', 'Cancel') ?></a>
            </div>

        </div>
    </div>
</form>
</div>

