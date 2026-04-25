<?php if (empty($notifications)): ?>
    <div class="text-center py-16 text-sm opacity-40">
        <?= __('notifications.no_notifications', 'No notifications yet') ?>
    </div>
<?php else: ?>
    <div class="flex flex-col gap-2" id="notif-page-list">
    <?php foreach ($notifications as $n): ?>
        <div class="flex items-start gap-3 p-4 rounded-lg border border-base-300 <?= $n->read_at ? 'opacity-60' : 'bg-base-200' ?>"
             data-id="<?= (int)$n->id ?>">
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium"><?= esc($n->title) ?></p>
                <p class="text-xs opacity-50 mt-0.5 whitespace-nowrap"><?= esc(substr($n->created_at, 0, 16)) ?></p>
            </div>
            <?php if ($n->url): ?>
            <div class="tooltip tooltip-left" data-tip="<?= __('notifications.open', 'Open') ?>">
                <a href="<?= esc($n->url) ?>" class="btn btn-ghost btn-xs btn-square">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                </a>
            </div>
            <?php endif ?>
            <?php if (!$n->read_at): ?>
            <div class="tooltip tooltip-left" data-tip="<?= __('notifications.mark_read', 'Mark as read') ?>">
                <button class="btn btn-ghost btn-xs btn-square notif-page-read" data-id="<?= (int)$n->id ?>"
                        data-url="<?= esc(route_to('app.notifications.read', $n->id)) ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                </button>
            </div>
            <?php endif ?>
        </div>
    <?php endforeach ?>
    </div>

    <div class="mt-4"><?= $pager->links() ?></div>
<?php endif ?>

<div id="notif-page-meta"
     data-mark-all-url="<?= esc(route_to('app.notifications.read_all')) ?>"
     hidden></div>
