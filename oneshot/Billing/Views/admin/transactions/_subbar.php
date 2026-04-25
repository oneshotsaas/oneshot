<div id="txn-filters-m" class="hidden sm:hidden sticky top-14 z-20 px-4 py-2 bg-base-100 border-b border-base-200 flex-wrap items-center gap-2">
    <form method="get" class="flex flex-wrap items-center gap-2 w-full" data-loading>
        <input type="text" name="user_id" value="<?= esc($filters['user_id'] ?? '') ?>" placeholder="<?= __('billing.filter_user', 'User ID') ?>" class="input input-bordered input-sm flex-1 min-w-[6rem]">
        <input type="text" name="action_prefix" value="<?= esc($filters['action_prefix'] ?? '') ?>" placeholder="<?= __('billing.filter_action', 'Action prefix') ?>" class="input input-bordered input-sm flex-1 min-w-[8rem]">
        <input type="date" name="date_from" value="<?= esc($filters['date_from'] ?? '') ?>" class="input input-bordered input-sm flex-1 min-w-[8rem]">
        <input type="date" name="date_to" value="<?= esc($filters['date_to'] ?? '') ?>" class="input input-bordered input-sm flex-1 min-w-[8rem]">
        <button class="btn btn-sm btn-primary"><?= __('billing.filter', 'Filter') ?></button>
        <a href="<?= route_to('admin.billing.transactions') ?>" class="btn btn-sm btn-ghost"><?= __('billing.reset', 'Reset') ?></a>
    </form>
</div>
