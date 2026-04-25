<form method="get" class="flex items-center gap-2">
    <input type="text"  name="user_id"   value="<?= esc($filters['userId']   ?? '') ?>" placeholder="<?= __('activity.filter_user', 'User ID') ?>"   class="input input-bordered input-sm w-24">
    <input type="text"  name="action"    value="<?= esc($filters['action']   ?? '') ?>" placeholder="<?= __('activity.filter_action', 'Action') ?>"  class="input input-bordered input-sm w-40">
    <input type="date"  name="date_from" value="<?= esc($filters['dateFrom'] ?? '') ?>"                                                                class="input input-bordered input-sm w-36">
    <input type="date"  name="date_to"   value="<?= esc($filters['dateTo']   ?? '') ?>"                                                                class="input input-bordered input-sm w-36">
    <button class="btn btn-sm btn-primary"><?= __('activity.filter_apply', 'Filter') ?></button>
    <a href="<?= route_to('admin.activity') ?>" class="btn btn-sm btn-ghost"><?= __('activity.filter_reset', 'Reset') ?></a>
</form>
