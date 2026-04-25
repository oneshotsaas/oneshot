<div class="max-w-2xl">
<form method="post">
    <?= csrf_field() ?>
    <div class="card bg-base-200 shadow">
        <div class="card-body p-4 sm:p-6 gap-4">

            <div class="grid grid-cols-1 sm:grid-cols-[12rem_1fr] items-start gap-x-6 gap-y-1">
                <span class="text-sm opacity-60"><?= __('billing.promo_code', 'Code') ?></span>
                <input type="text" name="code" value="<?= esc($promo->code ?? '') ?>" class="input input-sm input-bordered font-mono uppercase" required>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-[12rem_1fr] items-start gap-x-6 gap-y-1">
                <span class="text-sm opacity-60"><?= __('billing.description', 'Description') ?></span>
                <input type="text" name="description" value="<?= esc($promo->description ?? '') ?>" class="input input-sm input-bordered">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-[12rem_1fr] items-start gap-x-6 gap-y-1">
                <span class="text-sm opacity-60"><?= __('billing.discount_type', 'Discount Type') ?></span>
                <select name="discount_type" class="select select-sm select-bordered">
                    <option value="percent" <?= ($promo->discount_type ?? '') === 'percent' ? 'selected' : '' ?>><?= __('billing.percent', 'Percent') ?></option>
                    <option value="fixed" <?= ($promo->discount_type ?? '') === 'fixed' ? 'selected' : '' ?>><?= __('billing.fixed', 'Fixed') ?></option>
                </select>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-[12rem_1fr] items-start gap-x-6 gap-y-1">
                <span class="text-sm opacity-60"><?= __('billing.discount_value', 'Value') ?> <span class="opacity-50 text-xs">(% or cents)</span></span>
                <input type="number" name="discount_value" value="<?= (int)($promo->discount_value ?? 0) ?>" class="input input-sm input-bordered" required>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-[12rem_1fr] items-start gap-x-6 gap-y-1">
                <span class="text-sm opacity-60"><?= __('billing.applies_to', 'Applies To') ?></span>
                <select name="applies_to" class="select select-sm select-bordered">
                    <option value="all" <?= ($promo->applies_to ?? 'all') === 'all' ? 'selected' : '' ?>><?= __('billing.all', 'All') ?></option>
                    <option value="subscription" <?= ($promo->applies_to ?? '') === 'subscription' ? 'selected' : '' ?>><?= __('billing.subscription', 'Subscription') ?></option>
                    <option value="package" <?= ($promo->applies_to ?? '') === 'package' ? 'selected' : '' ?>><?= __('billing.package', 'Package') ?></option>
                </select>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-[12rem_1fr] items-start gap-x-6 gap-y-1">
                <span class="text-sm opacity-60"><?= __('billing.max_uses', 'Max Uses') ?> <span class="opacity-50 text-xs">(empty=∞)</span></span>
                <input type="number" name="max_uses" value="<?= esc($promo->max_uses ?? '') ?>" class="input input-sm input-bordered" min="1">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-[12rem_1fr] items-start gap-x-6 gap-y-1">
                <span class="text-sm opacity-60"><?= __('billing.used_count', 'Used Count') ?></span>
                <input type="number" name="used_count" value="<?= (int)($promo->used_count ?? 0) ?>" class="input input-sm input-bordered" min="0">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-[12rem_1fr] items-start gap-x-6 gap-y-1">
                <span class="text-sm opacity-60"><?= __('billing.valid_from', 'Valid From') ?></span>
                <input type="datetime-local" name="valid_from" value="<?= $promo->valid_from ?? '' ? str_replace(' ', 'T', substr($promo->valid_from, 0, 16)) : '' ?>" class="input input-sm input-bordered">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-[12rem_1fr] items-start gap-x-6 gap-y-1">
                <span class="text-sm opacity-60"><?= __('billing.valid_until', 'Valid Until') ?></span>
                <input type="datetime-local" name="valid_until" value="<?= !empty($promo->valid_until) ? str_replace(' ', 'T', substr($promo->valid_until, 0, 16)) : '' ?>" class="input input-sm input-bordered">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-[12rem_1fr] items-start gap-x-6 gap-y-1">
                <div>
                    <span class="text-sm opacity-60"><?= __('billing.subscription_discount_duration', 'Subscription Discount Duration') ?></span>
                    <p class="text-xs opacity-40 mt-1"><?= __('billing.subscription_discount_duration_hint', 'once = discount on first payment only; forever = discount on every payment') ?></p>
                </div>
                <select name="subscription_discount_duration" class="select select-sm select-bordered">
                    <option value="once" <?= ($promo->subscription_discount_duration ?? 'once') === 'once' ? 'selected' : '' ?>><?= __('billing.duration_once', 'once — first payment only') ?></option>
                    <option value="forever" <?= ($promo->subscription_discount_duration ?? '') === 'forever' ? 'selected' : '' ?>><?= __('billing.duration_forever', 'forever — all payments') ?></option>
                </select>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-[12rem_1fr] items-start gap-x-6 gap-y-1">
                <span class="text-sm opacity-60"><?= __('billing.active', 'Active') ?></span>
                <input type="checkbox" name="is_active" value="1" class="checkbox checkbox-sm" <?= !empty($promo->is_active) ? 'checked' : '' ?>>
            </div>

            <div class="pt-2">
                <button type="submit" class="btn btn-primary btn-sm"><?= __('billing.save', 'Save') ?></button>
            </div>

        </div>
    </div>
</form>
</div>
