<div class="rounded-lg border border-base-300 overflow-x-auto overflow-hidden">
    <table class="table table-sm w-full min-w-max">
        <thead>
            <tr class="bg-base-200 text-xs uppercase tracking-wider opacity-70">
                <th class="font-semibold">#</th>
                <th class="font-semibold"><?= __('billing.subscriber', 'User') ?></th>
                <th class="font-semibold"><?= __('billing.plan', 'Plan') ?></th>
                <th class="font-semibold"><?= __('billing.status', 'Status') ?></th>
                <th class="font-semibold text-right"><?= __('billing.balance', 'Balance') ?></th>
                <th class="font-semibold"><?= __('billing.period_end', 'Period End') ?></th>
                <th class="font-semibold"><?= __('billing.created_at', 'Created') ?></th>
                <th></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-base-200">
            <?php
            $statusColors = ['active'=>'badge-success','trial'=>'badge-info','past_due'=>'badge-warning','canceled'=>'badge-ghost','expired'=>'badge-error'];
            foreach ($subs as $sub): ?>
            <tr class="hover:bg-base-200/50 transition-colors">
                <td class="tabular-nums opacity-50 text-xs"><?= (int) $sub->id ?></td>
                <td class="font-medium">
                    <a href="<?= route_to('admin.billing.subscription', signId($sub->id)) ?>" class="hover:opacity-70 transition-opacity">
                        <?= esc($sub->user_id) ?>
                    </a>
                </td>
                <td class="opacity-70"><?= esc(isset($plans[$sub->plan_id]) ? $plans[$sub->plan_id]->name : $sub->plan_id) ?></td>
                <td><span class="badge badge-sm <?= $statusColors[$sub->status] ?? 'badge-ghost' ?>"><?= esc($sub->status) ?></span></td>
                <td class="text-right tabular-nums font-mono text-sm"><?= number_format((float)$sub->credits_balance, 2) ?></td>
                <td class="text-sm opacity-60 whitespace-nowrap"><?= $sub->current_period_end ? esc(substr($sub->current_period_end, 0, 10)) : '<span class="opacity-40">—</span>' ?></td>
                <td class="text-sm opacity-60 whitespace-nowrap"><?= esc(substr($sub->created_at, 0, 10)) ?></td>
                <td>
                    <div class="flex items-center justify-end">
                        <div class="tooltip tooltip-left" data-tip="<?= __('billing.view_all', 'View') ?>">
                            <a href="<?= route_to('admin.billing.subscription', signId($sub->id)) ?>" class="btn btn-ghost btn-xs btn-square">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            </a>
                        </div>
                    </div>
                </td>
            </tr>
            <?php endforeach ?>
            <?php if (empty($subs)): ?>
            <tr><td colspan="8" class="py-12 text-center text-sm opacity-40">—</td></tr>
            <?php endif ?>
        </tbody>
    </table>
</div>
<?= $pager->links() ?>
