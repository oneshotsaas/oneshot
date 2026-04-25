<template id="limit-row-tpl">
    <div class="flex items-center gap-2">
        <span class="text-sm opacity-50 shrink-0"><?= __('keys.limit_max', 'Max') ?></span>
        <input type="number" data-field="max" min="1" placeholder="1000" class="input input-sm input-bordered w-24 tabular-nums">
        <span class="text-sm opacity-50 shrink-0"><?= __('keys.limit_per', 'per') ?></span>
        <input type="number" data-field="days" min="0" placeholder="7" class="input input-sm input-bordered w-20 tabular-nums">
        <span class="text-sm opacity-50 shrink-0"><?= __('keys.limit_days_suffix', 'days (0 = all time)') ?></span>
        <button type="button" data-remove-limit class="btn btn-ghost btn-xs btn-square opacity-30 hover:opacity-80">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
        </button>
    </div>
</template>
