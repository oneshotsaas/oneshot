<?php
/**
 * App: API Keys list
 * Vars: $keys (array of key objects with ->usage attached)
 */
?>

<div class="rounded-lg border border-base-300 overflow-x-auto overflow-hidden">
    <table class="table table-sm w-full min-w-max">
        <thead>
            <tr class="bg-base-200 text-xs uppercase tracking-wider opacity-70">
                <th class="font-semibold"><?= __('keys.name', 'Name') ?></th>
                <th class="font-semibold"><?= __('keys.key_suffix', 'Key') ?></th>
                <th class="font-semibold"><?= __('keys.status', 'Status') ?></th>
                <th class="font-semibold"><?= __('keys.usage_label', 'Usage') ?></th>
                <th class="font-semibold text-sm opacity-60"><?= __('keys.expires_at', 'Expires') ?></th>
                <th class="font-semibold text-sm opacity-60"><?= __('keys.created', 'Created') ?></th>
                <th></th>
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
                <td>
                    <a href="<?= route_to('app.keys.edit', signId($key->id)) ?>"
                       class="font-medium hover:opacity-70 transition-opacity">
                        <?= esc($key->name) ?>
                    </a>
                </td>
                <td class="font-mono text-sm opacity-60">...<?= esc($key->key_suffix) ?></td>
                <td>
                    <?php if ($key->status === 'active'): ?>
                    <span class="badge badge-sm badge-success"><?= __('keys.active', 'Active') ?></span>
                    <?php else: ?>
                    <span class="badge badge-sm badge-warning"><?= __('keys.inactive', 'Inactive') ?></span>
                    <?php endif; ?>
                </td>
                <td class="tabular-nums text-sm">
                    <?= (int)$key->usage->requests ?> req / <?= (int)$key->usage->credits ?> cr
                </td>
                <td class="text-sm opacity-60 whitespace-nowrap">
                    <?= $key->expires_at ? esc(substr($key->expires_at, 0, 10)) : '<span class="opacity-30">—</span>' ?>
                </td>
                <td class="text-sm opacity-60 whitespace-nowrap">
                    <?= esc(substr($key->created_at, 0, 10)) ?>
                </td>
                <td>
                    <div class="flex items-center justify-end gap-1">
                        <div class="tooltip tooltip-left" data-tip="<?= __('keys.edit', 'Edit') ?>">
                            <a href="<?= route_to('app.keys.edit', signId($key->id)) ?>"
                               class="btn btn-ghost btn-xs btn-square">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                </svg>
                            </a>
                        </div>

                        <form method="post" action="<?= route_to('app.keys.toggle', signId($key->id)) ?>">
                            <?= csrf_field() ?>
                            <?php $tipToggle = $key->status === 'active' ? __('keys.deactivate', 'Deactivate') : __('keys.activate', 'Activate') ?>
                            <div class="tooltip tooltip-left" data-tip="<?= esc($tipToggle) ?>">
                                <button class="btn btn-ghost btn-xs btn-square">
                                    <?php if ($key->status === 'active'): ?>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                                         stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/>
                                    </svg>
                                    <?php else: ?>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                                         stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="12" r="10"/><polyline points="12 8 12 12 14 14"/>
                                    </svg>
                                    <?php endif; ?>
                                </button>
                            </div>
                        </form>

                        <form method="post" action="<?= route_to('app.keys.destroy', signId($key->id)) ?>"
                              onsubmit="return confirm('<?= __('keys.delete_confirm', 'Delete this key? This cannot be undone.') ?>')">
                            <?= csrf_field() ?>
                            <div class="tooltip tooltip-left" data-tip="<?= __('keys.delete', 'Delete') ?>">
                                <button class="btn btn-ghost btn-xs btn-square text-base-content/30 hover:text-error hover:bg-error/10 transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                                         stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="3 6 5 6 21 6"/>
                                        <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                                        <path d="M10 11v6"/><path d="M14 11v6"/>
                                        <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                                    </svg>
                                </button>
                            </div>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
