<div class="grid gap-6 max-w-3xl">

    <!-- Balance + Subscription -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="card bg-base-200 shadow">
            <div class="card-body">
                <p class="text-sm opacity-60"><?= __('billing.balance', 'Credit Balance') ?></p>
                <p class="text-3xl font-bold"><?= number_format((float)$balance, 2) ?></p>
                <div class="card-actions mt-2">
                    <a href="<?= route_to('billing.packages') ?>" class="btn btn-primary btn-sm"><?= __('billing.buy_credits', 'Buy Credits') ?></a>
                    <a href="<?= route_to('billing.usage') ?>" class="btn btn-ghost btn-sm"><?= __('billing.view_usage', 'Usage') ?></a>
                </div>
            </div>
        </div>

        <div class="card bg-base-200 shadow">
            <div class="card-body">
                <p class="text-sm opacity-60"><?= __('billing.current_plan', 'Current Plan') ?></p>
                <?php if ($sub): ?>
                    <p class="text-xl font-bold"><?= esc($plan->name ?? '—') ?></p>
                    <?php
                    $statusColors = ['active'=>'badge-success','trial'=>'badge-info','past_due'=>'badge-warning','canceled'=>'badge-ghost','expired'=>'badge-error'];
                    ?>
                    <span class="badge <?= $statusColors[$sub->status] ?? 'badge-ghost' ?>"><?= esc($sub->status) ?></span>
                    <?php if ($sub->trial_ends_at && $sub->status === 'trial'): ?>
                    <p class="text-xs opacity-60"><?= __('billing.trial_ends', 'Trial ends') ?>: <?= esc(substr($sub->trial_ends_at, 0, 10)) ?></p>
                    <?php elseif ($sub->current_period_end): ?>
                    <p class="text-xs opacity-60"><?= __('billing.period_ends', 'Period ends') ?>: <?= esc(substr($sub->current_period_end, 0, 10)) ?></p>
                    <?php endif ?>
                    <div class="card-actions mt-2">
                        <a href="<?= route_to('billing.plans') ?>" class="btn btn-ghost btn-sm"><?= __('billing.upgrade', 'Change Plan') ?></a>
                        <form method="post" action="<?= route_to('billing.cancel') ?>" onsubmit="return confirm('<?= __('billing.cancel_confirm', 'Are you sure?') ?>')">
                            <?= csrf_field() ?>
                            <button class="btn btn-error btn-outline btn-sm"><?= __('billing.cancel', 'Cancel') ?></button>
                        </form>
                    </div>
                <?php else: ?>
                    <p class="opacity-60"><?= __('billing.no_subscription', 'No active subscription') ?></p>
                    <div class="card-actions mt-2">
                        <a href="<?= route_to('billing.plans') ?>" class="btn btn-primary btn-sm"><?= __('billing.subscribe', 'Subscribe') ?></a>
                    </div>
                <?php endif ?>
            </div>
        </div>
    </div>

    <!-- Recent invoices -->
    <?php if (!empty($invoices)): ?>
    <div>
        <div class="flex items-center justify-between mb-3">
            <h3 class="font-semibold"><?= __('billing.recent_invoices', 'Recent Invoices') ?></h3>
            <a href="<?= route_to('billing.invoices') ?>" class="text-sm link link-primary"><?= __('billing.view_all', 'View all') ?></a>
        </div>
        <div class="rounded-lg border border-base-300 overflow-hidden">
            <table class="table table-sm w-full">
                <thead>
                    <tr class="bg-base-200 text-xs uppercase tracking-wider opacity-70">
                        <th class="font-semibold">#</th>
                        <th class="font-semibold text-right"><?= __('billing.amount', 'Amount') ?></th>
                        <th class="font-semibold"><?= __('billing.status', 'Status') ?></th>
                        <th class="font-semibold"><?= __('billing.date', 'Date') ?></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-base-200">
                    <?php
                    $statusColors = ['paid'=>'badge-success','open'=>'badge-warning','draft'=>'badge-ghost','void'=>'badge-error','uncollectible'=>'badge-error'];
                    foreach ($invoices as $inv): ?>
                    <tr class="hover:bg-base-200/50 transition-colors">
                        <td>
                            <a href="<?= route_to('billing.invoice', signId($inv->id)) ?>" class="font-medium hover:opacity-70 transition-opacity tabular-nums">
                                #<?= (int) $inv->id ?>
                            </a>
                        </td>
                        <td class="text-right tabular-nums font-medium">$<?= number_format($inv->amount / 100, 2) ?></td>
                        <td><span class="badge badge-sm <?= $statusColors[$inv->status] ?? 'badge-ghost' ?>"><?= esc($inv->status) ?></span></td>
                        <td class="text-sm opacity-60 whitespace-nowrap"><?= esc(substr($inv->created_at, 0, 10)) ?></td>
                    </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif ?>
</div>
