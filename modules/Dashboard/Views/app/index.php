<div class="max-w-4xl mx-auto">
    <h1 class="text-2xl font-bold mb-6"><?= __('dashboard.welcome', 'Welcome') ?>, <?= esc(session('user_name')) ?></h1>

    <?php if ($role === 'admin'): ?>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="card bg-base-200 shadow">
            <div class="card-body">
                <h2 class="card-title text-sm font-medium opacity-60"><?= __('dashboard.admin_panel', 'Admin Panel') ?></h2>
                <a href="<?= base_url(config('Prefixes')->admin . '/') ?>" class="btn btn-primary btn-sm mt-2">
                    <?= __('dashboard.go_to_admin', 'Go to Admin') ?>
                </a>
            </div>
        </div>
    </div>

    <?php else: ?>

    <div class="card bg-base-200 shadow">
        <div class="card-body">
            <p class="opacity-70"><?= __('dashboard.empty', 'Nothing here yet.') ?></p>
        </div>
    </div>

    <?php endif ?>
</div>
