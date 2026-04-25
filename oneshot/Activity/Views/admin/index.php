<div class="rounded-lg border border-base-300 overflow-x-auto overflow-hidden">
    <table class="table table-sm w-full min-w-max">
        <thead>
            <tr class="bg-base-200 text-xs uppercase tracking-wider opacity-70">
                <th class="font-semibold"><?= __('activity.date', 'Date') ?></th>
                <th class="font-semibold"><?= __('activity.actor', 'User') ?></th>
                <th class="font-semibold"><?= __('activity.action', 'Action') ?></th>
                <th class="font-semibold"><?= __('activity.subject', 'Subject') ?></th>
                <th class="font-semibold"><?= __('activity.ip', 'IP') ?></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-base-200">
            <?php foreach ($logs as $log): ?>
            <tr class="hover:bg-base-200/50 transition-colors">
                <td class="whitespace-nowrap text-sm tabular-nums opacity-70"><?= esc(substr($log->created_at, 0, 16)) ?></td>
                <td class="tabular-nums opacity-70"><?= $log->user_id ? (int)$log->user_id : '<span class="opacity-40">' . __('activity.system', 'System') . '</span>' ?></td>
                <td><span class="badge badge-ghost badge-sm font-mono"><?= esc($log->action) ?></span></td>
                <td class="text-sm opacity-60">
                    <?php if ($log->subject_type): ?>
                        <?= esc($log->subject_type) ?><?= $log->subject_id ? ' #' . (int)$log->subject_id : '' ?>
                    <?php else: ?>
                        <span class="opacity-40">—</span>
                    <?php endif ?>
                </td>
                <td class="text-sm font-mono opacity-50"><?= $log->ip ? esc($log->ip) : '<span class="opacity-30">—</span>' ?></td>
            </tr>
            <?php endforeach ?>
            <?php if (empty($logs)): ?>
            <tr><td colspan="5" class="py-12 text-center text-sm opacity-40"><?= __('activity.no_activity', 'No activity recorded.') ?></td></tr>
            <?php endif ?>
        </tbody>
    </table>
</div>
<?= $pager->links() ?>
