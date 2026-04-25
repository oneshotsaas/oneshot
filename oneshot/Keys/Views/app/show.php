<?php
/**
 * App: Show newly created API key (one-time display)
 * Vars: $key, $new_key
 */
?>

<div class="max-w-xl flex flex-col gap-4">

    <div class="alert alert-warning">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none"
             stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
            <line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
        </svg>
        <span><?= __('keys.show_notice', 'Copy this key now — it will never be shown again.') ?></span>
    </div>

    <div class="card bg-base-200 rounded-xl">
        <div class="card-body gap-4">
            <div class="grid grid-cols-1 sm:grid-cols-[12rem_1fr] items-start gap-x-6 gap-y-1">
                <span class="text-sm opacity-60 pt-1"><?= __('keys.name', 'Name') ?></span>
                <span class="text-sm font-medium"><?= esc($key->name) ?></span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-[12rem_1fr] items-start gap-x-6 gap-y-1">
                <span class="text-sm opacity-60 pt-1"><?= __('keys.api_key', 'API Key') ?></span>
                <div>
                    <div class="relative">
                        <input id="new-key-input" type="text" value="<?= esc($new_key) ?>"
                               class="input input-sm input-bordered font-mono w-full pr-20" readonly>
                        <div class="absolute inset-y-0 right-0 flex items-center pr-1">
                            <button type="button"
                                    class="btn btn-ghost btn-xs btn-square opacity-60 hover:opacity-100 js-copy-value">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 icon-copy" viewBox="0 0 24 24"
                                     fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="9" y="9" width="13" height="13" rx="2" ry="2"/>
                                    <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
                                </svg>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 icon-check hidden" viewBox="0 0 24 24"
                                     fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="20 6 9 17 4 12"/>
                                </svg>
                            </button>
                            <span class="text-xs font-medium pr-1"><?= __('keys.copy', 'Copy') ?></span>
                        </div>
                    </div>
                    <p class="text-xs opacity-40 mt-1"><?= __('keys.key_format_hint', 'Keep this secret. Use it in X-API-Key header or Authorization: Bearer.') ?></p>
                </div>
            </div>
        </div>
    </div>

    <div>
        <a href="<?= route_to('app.keys') ?>" class="btn btn-ghost btn-sm">
            &larr; <?= __('keys.back_to_keys', 'Go to API Keys') ?>
        </a>
    </div>
</div>
