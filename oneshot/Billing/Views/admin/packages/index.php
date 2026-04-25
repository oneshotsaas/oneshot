<div class="rounded-lg border border-base-300 overflow-x-auto overflow-hidden">
    <table class="table table-sm w-full min-w-max">
        <thead>
            <tr class="bg-base-200 text-xs uppercase tracking-wider opacity-70">
                <th class="font-semibold"><?= __('billing.package_name', 'Name') ?></th>
                <th class="font-semibold text-right"><?= __('billing.credits', 'Credits') ?></th>
                <th class="font-semibold text-right"><?= __('billing.price', 'Price') ?></th>
                <th class="font-semibold text-right"><?= __('billing.old_price', 'Old Price') ?></th>
                <th class="font-semibold"><?= __('billing.badge', 'Badge') ?></th>
                <th class="font-semibold text-right"><?= __('billing.sort', 'Sort') ?></th>
                <th class="font-semibold"><?= __('billing.status', 'Status') ?></th>
                <th></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-base-200">
            <?php foreach ($packages as $pkg): ?>
            <tr class="hover:bg-base-200/50 transition-colors">
                <td>
                    <a href="<?= route_to('admin.billing.package.edit', signId($pkg->id)) ?>" class="font-medium hover:opacity-70 transition-opacity">
                        <?= esc($pkg->name) ?>
                    </a>
                </td>
                <td class="text-right tabular-nums"><?= number_format($pkg->credits) ?></td>
                <td class="text-right tabular-nums">$<?= esc($pkg->price_ui) ?></td>
                <td class="text-right tabular-nums opacity-50"><?= $pkg->old_price ? '$' . esc($pkg->old_price_ui) : '—' ?></td>
                <td><?= $pkg->badge ? '<span class="badge badge-accent badge-sm">' . esc($pkg->badge) . '</span>' : '<span class="opacity-30">—</span>' ?></td>
                <td class="text-right tabular-nums opacity-50"><?= (int) $pkg->sort ?></td>
                <td><span class="badge badge-sm <?= $pkg->is_active ? 'badge-success' : 'badge-ghost' ?>"><?= $pkg->is_active ? __('billing.active', 'Active') : __('billing.inactive', 'Inactive') ?></span></td>
                <td>
                    <div class="flex items-center justify-end gap-1">
                        <div class="tooltip tooltip-left" data-tip="<?= __('billing.edit', 'Edit') ?>">
                            <a href="<?= route_to('admin.billing.package.edit', signId($pkg->id)) ?>" class="btn btn-ghost btn-xs btn-square">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                            </a>
                        </div>
                        <form method="post" action="<?= route_to('admin.billing.package.delete', signId($pkg->id)) ?>" onsubmit="return confirm('Delete?')">
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
            <?php if (empty($packages)): ?>
            <tr><td colspan="8" class="py-12 text-center text-sm opacity-40">—</td></tr>
            <?php endif ?>
        </tbody>
    </table>
</div>
