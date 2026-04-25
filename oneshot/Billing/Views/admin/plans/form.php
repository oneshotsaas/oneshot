<div class="max-w-2xl">
<form method="post">
    <?= csrf_field() ?>
    <div class="card bg-base-200 shadow">
        <div class="card-body p-4 sm:p-6 gap-4">

            <div class="grid grid-cols-1 sm:grid-cols-[12rem_1fr] items-start gap-x-6 gap-y-1">
                <span class="text-sm opacity-60"><?= __('billing.plan_name', 'Plan Name') ?></span>
                <input type="text" name="name" value="<?= esc($plan->name ?? '') ?>" class="input input-sm input-bordered" required>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-[12rem_1fr] items-start gap-x-6 gap-y-1">
                <span class="text-sm opacity-60"><?= __('billing.plan_slug', 'Slug') ?></span>
                <input type="text" name="slug" value="<?= esc($plan->slug ?? '') ?>" class="input input-sm input-bordered" required>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-[12rem_1fr] items-start gap-x-6 gap-y-1">
                <span class="text-sm opacity-60 pt-1"><?= __('billing.description', 'Description') ?></span>
                <textarea name="description" rows="3" class="textarea textarea-sm textarea-bordered"><?= esc($plan->description ?? '') ?></textarea>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-[12rem_1fr] items-start gap-x-6 gap-y-1">
                <span class="text-sm opacity-60"><?= __('billing.credits_included', 'Credits Included') ?></span>
                <input type="number" name="credits_included" value="<?= (int)($plan->credits_included ?? 0) ?>" class="input input-sm input-bordered" min="0" required>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-[12rem_1fr] items-start gap-x-6 gap-y-1">
                <span class="text-sm opacity-60"><?= __('billing.trial_days', 'Trial Days') ?></span>
                <input type="number" name="trial_days" value="<?= (int)($plan->trial_days ?? 0) ?>" class="input input-sm input-bordered" min="0">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-[12rem_1fr] items-start gap-x-6 gap-y-1">
                <div>
                    <span class="text-sm opacity-60"><?= __('billing.trial_credits', 'Trial Credits') ?></span>
                    <p class="text-xs opacity-40"><?= __('billing.trial_credits_hint', 'Leave empty for proportional (trial_days / 30 × credits_included)') ?></p>
                </div>
                <input type="number" name="trial_credits" value="<?= esc($plan->trial_credits ?? '') ?>" class="input input-sm input-bordered" min="0" placeholder="auto">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-[12rem_1fr] items-start gap-x-6 gap-y-1">
                <span class="text-sm opacity-60 pt-1"><?= __('billing.features', 'Features') ?> <span class="opacity-50 text-xs">(one per line)</span></span>
                <?php $features = !empty($plan->features) ? (json_decode($plan->features, true) ?: []) : [] ?>
                <textarea name="features" rows="5" class="textarea textarea-sm textarea-bordered font-mono"><?= esc(implode("\n", $features)) ?></textarea>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-[12rem_1fr] items-start gap-x-6 gap-y-1">
                <span class="text-sm opacity-60"><?= __('billing.badge', 'Badge') ?> <span class="opacity-50 text-xs">(e.g. Popular)</span></span>
                <input type="text" name="badge" value="<?= esc($plan->badge ?? '') ?>" class="input input-sm input-bordered">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-[12rem_1fr] items-start gap-x-6 gap-y-1">
                <span class="text-sm opacity-60"><?= __('billing.sort', 'Sort Order') ?></span>
                <input type="number" name="sort" value="<?= (int)($plan->sort ?? 0) ?>" class="input input-sm input-bordered">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-[12rem_1fr] items-start gap-x-6 gap-y-1">
                <span class="text-sm opacity-60"><?= __('billing.active', 'Active') ?></span>
                <input type="checkbox" name="is_active" value="1" class="checkbox checkbox-sm" <?= !empty($plan->is_active) ? 'checked' : '' ?>>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-[12rem_1fr] items-start gap-x-6 gap-y-1">
                <span class="text-sm opacity-60"><?= __('billing.hide_price', 'Hide Price') ?></span>
                <input type="checkbox" name="hide_price" value="1" class="checkbox checkbox-sm" <?= !empty($plan->hide_price) ? 'checked' : '' ?>>
            </div>

            <div class="pt-2">
                <button type="submit" class="btn btn-primary btn-sm"><?= __('billing.save', 'Save') ?></button>
            </div>

        </div>
    </div>
</form>
</div>
