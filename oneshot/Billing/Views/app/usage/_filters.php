<!-- Desktop filters (hidden on mobile) -->
<form method="get" class="hidden sm:flex items-center gap-2">
    <select name="action_prefix" class="select select-bordered select-sm w-40">
        <option value=""><?= __('billing.all_actions', 'All Actions') ?></option>
        <option value="image." <?= ($filters['action_prefix'] ?? '') === 'image.' ? 'selected' : '' ?>><?= __('billing.action_image', 'Image') ?></option>
        <option value="video." <?= ($filters['action_prefix'] ?? '') === 'video.' ? 'selected' : '' ?>><?= __('billing.action_video', 'Video') ?></option>
        <option value="llm."   <?= ($filters['action_prefix'] ?? '') === 'llm.'   ? 'selected' : '' ?>><?= __('billing.action_llm', 'LLM') ?></option>
    </select>
    <input type="date" name="date_from" value="<?= esc($filters['date_from'] ?? '') ?>" class="input input-bordered input-sm w-36">
    <input type="date" name="date_to"   value="<?= esc($filters['date_to']   ?? '') ?>" class="input input-bordered input-sm w-36">
    <button class="btn btn-sm btn-primary" data-loading><?= __('billing.filter', 'Filter') ?></button>
    <a href="<?= route_to('billing.usage') ?>" class="btn btn-sm btn-ghost"><?= __('billing.reset', 'Reset') ?></a>
</form>
<!-- Mobile toggle button -->
<div class="tooltip tooltip-bottom sm:hidden" data-tip="<?= __('billing.filters', 'Filters') ?>">
    <button type="button" class="btn btn-ghost btn-sm btn-square"
            onclick="var f=document.getElementById('usage-filters-m');f.classList.toggle('hidden');f.classList.toggle('flex')">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 4h18M7 8h10M11 12h2M9 16h6"/>
        </svg>
    </button>
</div>
