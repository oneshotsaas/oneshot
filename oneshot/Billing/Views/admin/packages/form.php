<div class="max-w-2xl">
<form method="post">
    <?= csrf_field() ?>
    <div class="card bg-base-200 shadow">
        <div class="card-body p-4 sm:p-6 gap-4">

            <div class="grid grid-cols-1 sm:grid-cols-[12rem_1fr] items-start gap-x-6 gap-y-1">
                <span class="text-sm opacity-60"><?= __('billing.package_name', 'Name') ?></span>
                <input type="text" name="name" value="<?= esc($package->name ?? '') ?>" class="input input-sm input-bordered" required>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-[12rem_1fr] items-start gap-x-6 gap-y-1">
                <span class="text-sm opacity-60"><?= __('billing.credits', 'Credits') ?></span>
                <input type="number" name="credits" value="<?= (int)($package->credits ?? 0) ?>" class="input input-sm input-bordered" min="1" required>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-[12rem_1fr] items-start gap-x-6 gap-y-1">
                <span class="text-sm opacity-60"><?= __('billing.price', 'Price') ?> ($)</span>
                <input type="text" name="price" value="<?= esc($package->price_ui ?? '') ?>" class="input input-sm input-bordered" required>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-[12rem_1fr] items-start gap-x-6 gap-y-1">
                <span class="text-sm opacity-60"><?= __('billing.old_price', 'Old Price') ?> ($)</span>
                <input type="text" name="old_price" value="<?= esc($package->old_price_ui ?? '') ?>" class="input input-sm input-bordered">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-[12rem_1fr] items-start gap-x-6 gap-y-1">
                <span class="text-sm opacity-60"><?= __('billing.currency', 'Currency') ?></span>
                <input type="text" name="currency" value="<?= esc($package->currency ?? 'usd') ?>" class="input input-sm input-bordered" maxlength="3">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-[12rem_1fr] items-start gap-x-6 gap-y-1">
                <span class="text-sm opacity-60"><?= __('billing.badge', 'Badge') ?></span>
                <input type="text" name="badge" value="<?= esc($package->badge ?? '') ?>" class="input input-sm input-bordered">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-[12rem_1fr] items-start gap-x-6 gap-y-1">
                <span class="text-sm opacity-60"><?= __('billing.sort', 'Sort') ?></span>
                <input type="number" name="sort" value="<?= (int)($package->sort ?? 0) ?>" class="input input-sm input-bordered">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-[12rem_1fr] items-start gap-x-6 gap-y-1">
                <span class="text-sm opacity-60"><?= __('billing.active', 'Active') ?></span>
                <input type="checkbox" name="is_active" value="1" class="checkbox checkbox-sm" <?= !empty($package->is_active) ? 'checked' : '' ?>>
            </div>

            <div class="pt-2">
                <button type="submit" class="btn btn-primary btn-sm"><?= __('billing.save', 'Save') ?></button>
            </div>

        </div>
    </div>
</form>
</div>
