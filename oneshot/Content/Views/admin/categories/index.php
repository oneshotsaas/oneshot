<?php
$slugWarning = session()->getFlashdata('content_slug_warning');
if ($slugWarning): ?>
<div class="alert alert-warning mb-4"><?= esc($slugWarning) ?></div>
<?php endif ?>

<div class="rounded-lg border border-base-300 overflow-x-auto overflow-hidden">
    <table class="table table-sm w-full min-w-max">
        <thead>
            <tr class="bg-base-200 text-xs uppercase tracking-wider opacity-70">
                <th class="font-semibold"><?= __('content.title', 'Title') ?></th>
                <th class="font-semibold"><?= __('content.slug', 'Slug') ?></th>
                <th class="font-semibold"><?= __('content.parent', 'Parent') ?></th>
                <th class="font-semibold text-right"><?= __('content.sort', 'Sort') ?></th>
                <th class="font-semibold"><?= __('billing.status', 'Status') ?></th>
                <th></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-base-200">
            <?php
            $catIndex = [];
            foreach ($categories as $c) $catIndex[$c->id] = $c;
            foreach ($categories as $cat): ?>
            <tr class="hover:bg-base-200/50 transition-colors">
                <td>
                    <a href="<?= route_to('admin.content.categories.edit', signId($cat->id)) ?>" class="font-medium hover:opacity-70 transition-opacity">
                        <?php if ($cat->parent_id): ?>
                        <span class="opacity-30 mr-1">↳</span>
                        <?php endif ?>
                        <?= esc($cat->title) ?>
                    </a>
                </td>
                <td class="font-mono text-xs">
                    <?php $catUrl = content_url($cat, 'category') ?>
                    <a href="<?= $catUrl ?>" target="_blank" rel="noopener" class="opacity-60 hover:opacity-100 transition-opacity"><?= esc(parse_url($catUrl, PHP_URL_PATH)) ?></a>
                </td>
                <td class="text-sm opacity-60"><?= $cat->parent_id && isset($catIndex[$cat->parent_id]) ? esc($catIndex[$cat->parent_id]->title) : '—' ?></td>
                <td class="text-right tabular-nums opacity-50"><?= (int)$cat->sort ?></td>
                <td><span class="badge badge-sm <?= $cat->is_active ? 'badge-success' : 'badge-ghost' ?>"><?= $cat->is_active ? __('content.active', 'Active') : __('content.inactive', 'Inactive') ?></span></td>
                <td>
                    <div class="flex items-center justify-end gap-1">
                        <div class="tooltip tooltip-left" data-tip="<?= __('content.view', 'View') ?>">
                            <a href="<?= $catUrl ?>" target="_blank" rel="noopener" class="btn btn-ghost btn-xs btn-square">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                            </a>
                        </div>
                        <form method="post" action="<?= route_to('admin.content.categories.delete', signId($cat->id)) ?>" onsubmit="return confirm('<?= __('content.delete_confirm', 'Delete?') ?>')">
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
            <?php if (empty($categories)): ?>
            <tr><td colspan="6" class="py-12 text-center text-sm opacity-40"><?= __('content.no_categories', 'No categories yet') ?></td></tr>
            <?php endif ?>
        </tbody>
    </table>
</div>
