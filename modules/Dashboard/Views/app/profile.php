<div class="max-w-lg mx-auto">
    <h1 class="text-2xl font-bold mb-6"><?= __('dashboard.profile', 'Profile') ?></h1>

    <div class="card bg-base-200 shadow">
        <div class="card-body gap-3">
            <div>
                <span class="text-sm opacity-60"><?= __('dashboard.name', 'Name') ?></span>
                <p class="font-medium"><?= esc(session('user_name')) ?></p>
            </div>
            <div>
                <span class="text-sm opacity-60"><?= __('dashboard.role', 'Role') ?></span>
                <p class="font-medium"><?= esc(session('user_role')) ?></p>
            </div>
        </div>
    </div>
</div>
