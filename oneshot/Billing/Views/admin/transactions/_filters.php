<form method="get" class="flex items-center gap-2">
    <input type="text" name="user_id" value="<?= esc($filters['user_id'] ?? '') ?>" placeholder="<?= __('billing.filter_user', 'User ID') ?>" class="input input-bordered input-sm w-24">
    <input type="text" name="action_prefix" value="<?= esc($filters['action_prefix'] ?? '') ?>" placeholder="<?= __('billing.filter_action', 'Action prefix') ?>" class="input input-bordered input-sm w-36">
    <input type="date" name="date_from" value="<?= esc($filters['date_from'] ?? '') ?>" class="input input-bordered input-sm w-36">
    <input type="date" name="date_to" value="<?= esc($filters['date_to'] ?? '') ?>" class="input input-bordered input-sm w-36">
    <button class="btn btn-sm btn-primary"><?= __('billing.filter', 'Filter') ?></button>
    <a href="<?= route_to('admin.billing.transactions') ?>" class="btn btn-sm btn-ghost"><?= __('billing.reset', 'Reset') ?></a>
</form>
