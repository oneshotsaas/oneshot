<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    <div class="card bg-base-200 shadow">
        <div class="card-body">
            <h2 class="card-title text-sm font-medium opacity-60"><?= __('dashboard.users', 'Users') ?></h2>
            <a href="<?= route_to('admin.users') ?>" class="btn btn-primary btn-sm mt-2">
                <?= __('dashboard.manage_users', 'Manage Users') ?>
            </a>
        </div>
    </div>
</div>
