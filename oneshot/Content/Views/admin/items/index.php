<div class="rounded-lg border border-base-300 overflow-x-auto overflow-hidden">
    <table class="table table-sm w-full min-w-max">
        <thead>
            <tr class="bg-base-200 text-xs uppercase tracking-wider opacity-70">
                <th class="font-semibold"><?= __('content.title', 'Title') ?></th>
                <th class="font-semibold"><?= __('content.slug', 'Slug') ?></th>
                <th class="font-semibold"><?= __('content.type', 'Type') ?></th>
                <th class="font-semibold"><?= __('billing.status', 'Status') ?></th>
                <th class="font-semibold opacity-50"><?= __('content.created', 'Created') ?></th>
                <th></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-base-200">
            <?php foreach ($items as $item): ?>
            <tr class="hover:bg-base-200/50 transition-colors">
                <td>
                    <?php $backQ = array_filter(['type' => $filterType ?? '', 'cat' => $filterCat ?? '']); $backUrl = route_to('admin.content.items') . ($backQ ? '?' . http_build_query($backQ) : ''); ?>
                    <a href="<?= route_to('admin.content.items.edit', signId($item->id)) . '?back=' . urlencode($backUrl) ?>" class="font-medium hover:opacity-70 transition-opacity">
                        <?= esc($item->title) ?>
                    </a>
                </td>
                <td class="font-mono text-xs opacity-60">
                    <a href="<?= content_url($item, 'item') ?>" target="_blank" rel="noopener" class="hover:opacity-100 opacity-60 transition-opacity"><?= esc($item->slug) ?></a>
                </td>
                <td class="text-xs opacity-60"><?= esc($item->type) ?></td>
                <td><span class="badge badge-sm <?= $item->is_active ? 'badge-success' : 'badge-ghost' ?>"><?= $item->is_active ? __('content.active', 'Active') : __('content.inactive', 'Inactive') ?></span></td>
                <td class="text-xs opacity-40"><?= date('d M Y', strtotime($item->created_at)) ?></td>
                <td>
                    <div class="flex items-center justify-end gap-1">
                        <div class="tooltip tooltip-left" data-tip="<?= __('content.view', 'View') ?>">
                            <a href="<?= content_url($item, 'item') ?>" target="_blank" rel="noopener" class="btn btn-ghost btn-xs btn-square">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                            </a>
                        </div>
                        <form method="post" action="<?= route_to('admin.content.items.delete', signId($item->id)) ?>" onsubmit="return confirm('<?= __('content.delete_confirm', 'Delete?') ?>')">
                            <?= csrf_field() ?>
                            <div class="tooltip tooltip-left" data-tip="<?= __('content.delete', 'Delete') ?>">
                                <button class="btn btn-ghost btn-xs btn-square text-base-content/30 hover:text-error hover:bg-error/10 transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                                </button>
                            </div>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endforeach ?>
            <?php if (empty($items)): ?>
            <tr><td colspan="6" class="py-12 text-center text-sm opacity-40"><?= __('content.no_items', 'No items yet') ?></td></tr>
            <?php endif ?>
        </tbody>
    </table>
</div>
