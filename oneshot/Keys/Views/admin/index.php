<?php
/**
 * Admin: API Keys list (all users)
 * Vars: $keys
 */
?>

<div class="rounded-lg border border-base-300 overflow-x-auto overflow-hidden">
    <table class="table table-sm w-full min-w-max">
        <thead>
            <tr class="bg-base-200 text-xs uppercase tracking-wider opacity-70">
                <th class="font-semibold"><?= __('keys.user', 'User') ?></th>
                <th class="font-semibold"><?= __('keys.name', 'Name') ?></th>
                <th class="font-semibold"><?= __('keys.key_suffix', 'Key') ?></th>
                <th class="font-semibold"><?= __('keys.status', 'Status') ?></th>
                <th class="font-semibold"><?= __('keys.limits_summary', 'Limits') ?></th>
                <th class="font-semibold text-sm opacity-60"><?= __('keys.last_used', 'Last Used') ?></th>
                <th class="font-semibold text-sm opacity-60"><?= __('keys.created', 'Created') ?></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-base-200">
            <?php if (empty($keys)): ?>
            <tr>
                <td colspan="7" class="py-12 text-center text-sm opacity-40">
                    <?= __('keys.no_keys', 'No API keys yet.') ?>
                </td>
            </tr>
            <?php else: ?>
            <?php foreach ($keys as $key): ?>
            <tr class="hover:bg-base-200/50 transition-colors">
                <td class="text-sm"><?= esc($key->user_email) ?></td>
                <td class="font-medium"><?= esc($key->name) ?></td>
                <td class="font-mono text-sm opacity-60">...<?= esc($key->key_suffix) ?></td>
                <td>
                    <?php if ($key->status === 'active'): ?>
                    <span class="badge badge-sm badge-success"><?= __('keys.active', 'Active') ?></span>
                    <?php else: ?>
                    <span class="badge badge-sm badge-warning"><?= __('keys.inactive', 'Inactive') ?></span>
                    <?php endif; ?>
                </td>
                <td class="text-sm opacity-60">
                    <?php
                    $reqLimits = json_decode($key->limits_requests, true) ?? [];
                    $credLimits = json_decode($key->limits_credits, true) ?? [];
                    $parts = [];
                    foreach ($reqLimits as $l) {
                        $parts[] = ($l['days'] == 0 ? 'total' : $l['days'] . 'd') . ':' . $l['max'] . 'req';
                    }
                    foreach ($credLimits as $l) {
                        $parts[] = ($l['days'] == 0 ? 'total' : $l['days'] . 'd') . ':' . $l['max'] . 'cr';
                    }
                    echo $parts ? esc(implode(', ', $parts)) : '<span class="opacity-30">—</span>';
                    ?>
                </td>
                <td class="text-sm opacity-60 whitespace-nowrap">
                    <?= $key->last_used_at ? esc(substr($key->last_used_at, 0, 16)) : '<span class="opacity-30">—</span>' ?>
                </td>
                <td class="text-sm opacity-60 whitespace-nowrap">
                    <?= esc(substr($key->created_at, 0, 10)) ?>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
