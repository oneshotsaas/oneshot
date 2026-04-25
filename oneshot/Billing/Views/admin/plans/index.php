<div class="rounded-lg border border-base-300 overflow-x-auto overflow-hidden">
    <table class="table table-sm w-full min-w-max">
        <thead>
            <tr class="bg-base-200 text-xs uppercase tracking-wider opacity-70">
                <th class="font-semibold"><?= __('billing.plan_name', 'Plan Name') ?></th>
                <th class="font-semibold"><?= __('billing.plan_slug', 'Slug') ?></th>
                <th class="font-semibold text-right"><?= __('billing.credits_included', 'Credits') ?></th>
                <th class="font-semibold text-right"><?= __('billing.trial_days', 'Trial') ?></th>
                <th class="font-semibold text-right"><?= __('billing.sort', 'Sort') ?></th>
                <th class="font-semibold"><?= __('billing.status', 'Status') ?></th>
                <th></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-base-200">
            <?php foreach ($plans as $plan): ?>
            <tr class="hover:bg-base-200/50 transition-colors">
                <td>
                    <a href="<?= route_to('admin.billing.plan.edit', signId($plan->id)) ?>" class="font-medium hover:opacity-70 transition-opacity">
                        <?= esc($plan->name) ?>
                        <?php if ($plan->badge): ?><span class="badge badge-accent badge-sm ml-1"><?= esc($plan->badge) ?></span><?php endif ?>
                    </a>
                </td>
                <td class="font-mono text-xs opacity-60"><?= esc($plan->slug) ?></td>
                <td class="text-right tabular-nums"><?= number_format($plan->credits_included) ?></td>
                <td class="text-right tabular-nums"><?= (int) $plan->trial_days ?>d</td>
                <td class="text-right tabular-nums opacity-50"><?= (int) $plan->sort ?></td>
                <td><span class="badge badge-sm <?= $plan->is_active ? 'badge-success' : 'badge-ghost' ?>"><?= $plan->is_active ? __('billing.active', 'Active') : __('billing.inactive', 'Inactive') ?></span></td>
                <td>
                    <div class="flex items-center justify-end gap-1">
                        <div class="tooltip tooltip-left" data-tip="<?= __('billing.prices', 'Prices') ?>">
                            <a href="<?= route_to('admin.billing.plan.prices', signId($plan->id)) ?>" class="btn btn-ghost btn-xs btn-square">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
                            </a>
                        </div>
                        <form method="post" action="<?= route_to('admin.billing.plan.delete', signId($plan->id)) ?>" onsubmit="return confirm('Delete?')">
                            <?= csrf_field() ?>
                            <div class="tooltip tooltip-left" data-tip="<?= __('billing.delete', 'Delete') ?>">
                                <button class="btn btn-ghost btn-xs btn-square text-base-content/30 hover:text-error hover:bg-error/10 transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                                </button>
                            </div>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endforeach ?>
            <?php if (empty($plans)): ?>
            <tr><td colspan="7" class="py-12 text-center text-sm opacity-40"><?= __('billing.no_plans', 'No plans available') ?></td></tr>
            <?php endif ?>
        </tbody>
    </table>
</div>
