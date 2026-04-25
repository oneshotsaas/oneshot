<!-- Desktop filters (hidden on mobile) -->
<form method="get" class="hidden sm:flex items-center gap-2">
    <input type="text" name="user_id" value="<?= esc($filters['user_id'] ?? '') ?>" placeholder="<?= __('billing.filter_user', 'User ID') ?>" class="input input-bordered input-sm w-24">
    <input type="text" name="action_prefix" value="<?= esc($filters['action_prefix'] ?? '') ?>" placeholder="<?= __('billing.filter_action', 'Action prefix') ?>" class="input input-bordered input-sm w-36">
    <input type="date" name="date_from" value="<?= esc($filters['date_from'] ?? '') ?>" class="input input-bordered input-sm w-36">
    <input type="date" name="date_to" value="<?= esc($filters['date_to'] ?? '') ?>" class="input input-bordered input-sm w-36">
    <button class="btn btn-sm btn-primary"><?= __('billing.filter', 'Filter') ?></button>
    <a href="<?= route_to('admin.billing.transactions') ?>" class="btn btn-sm btn-ghost"><?= __('billing.reset', 'Reset') ?></a>
</form>
<!-- Mobile toggle button -->
<div class="tooltip tooltip-bottom sm:hidden" data-tip="<?= __('billing.filters', 'Filters') ?>">
    <button type="button" class="btn btn-ghost btn-sm btn-square"
            onclick="var f=document.getElementById('txn-filters-m');f.classList.toggle('hidden');f.classList.toggle('flex')">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 4h18M7 8h10M11 12h2M9 16h6"/>
        </svg>
    </button>
</div>
