<div class="max-w-2xl grid gap-4">
    <div class="card bg-base-200 shadow">
        <div class="card-body gap-3">
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <span class="text-sm opacity-60"><?= __('billing.subscriber', 'User') ?></span>
                    <p class="font-medium"><?= esc($sub->user_id) ?></p>
                </div>
                <div>
                    <span class="text-sm opacity-60"><?= __('billing.plan', 'Plan') ?></span>
                    <p class="font-medium"><?= esc($plan->name ?? $sub->plan_id) ?></p>
                </div>
                <div>
                    <span class="text-sm opacity-60"><?= __('billing.interval', 'Interval') ?></span>
                    <p class="font-medium"><?= esc($price->interval ?? '—') ?></p>
                </div>
                <div>
                    <span class="text-sm opacity-60"><?= __('billing.status', 'Status') ?></span>
                    <p><?php $statusColors = ['active'=>'badge-success','trial'=>'badge-info','past_due'=>'badge-warning','canceled'=>'badge-ghost','expired'=>'badge-error'] ?>
                    <span class="badge <?= $statusColors[$sub->status] ?? 'badge-ghost' ?>"><?= esc($sub->status) ?></span></p>
                </div>
                <div>
                    <span class="text-sm opacity-60"><?= __('billing.balance', 'Balance') ?></span>
                    <p class="font-medium"><?= number_format((float)$sub->credits_balance, 2) ?></p>
                </div>
                <div>
                    <span class="text-sm opacity-60"><?= __('billing.overdraft_used', 'Overdraft Used') ?></span>
                    <p><?= $sub->overdraft_used ? __('billing.yes', 'Yes') : __('billing.no', 'No') ?></p>
                </div>
                <div>
                    <span class="text-sm opacity-60"><?= __('billing.period_start', 'Period Start') ?></span>
                    <p><?= esc($sub->current_period_start ?? '—') ?></p>
                </div>
                <div>
                    <span class="text-sm opacity-60"><?= __('billing.period_end', 'Period End') ?></span>
                    <p><?= esc($sub->current_period_end ?? '—') ?></p>
                </div>
                <?php if ($sub->canceled_at): ?>
                <div>
                    <span class="text-sm opacity-60"><?= __('billing.canceled_at', 'Canceled At') ?></span>
                    <p><?= esc($sub->canceled_at) ?></p>
                </div>
                <?php endif ?>
                <div>
                    <span class="text-sm opacity-60"><?= __('billing.created_at', 'Created') ?></span>
                    <p><?= esc($sub->created_at) ?></p>
                </div>
            </div>

            <?php $features = json_decode($sub->features_snapshot ?? '[]', true) ?: []; ?>
            <?php if (!empty($features)): ?>
            <div>
                <span class="text-sm opacity-60"><?= __('billing.features', 'Features') ?></span>
                <ul class="mt-1 list-disc list-inside text-sm">
                    <?php foreach ($features as $f): ?>
                    <li><?= esc($f) ?></li>
                    <?php endforeach ?>
                </ul>
            </div>
            <?php endif ?>
        </div>
    </div>

    <div class="flex gap-2">
        <a href="<?= route_to('admin.billing.topup', signId($sub->user_id)) ?>" class="btn btn-sm btn-outline"><?= __('billing.topup', 'Top Up') ?></a>
        <a href="<?= route_to('admin.billing.subscriptions') ?>" class="btn btn-sm btn-ghost"><?= __('billing.back', 'Back') ?></a>
    </div>
</div>
