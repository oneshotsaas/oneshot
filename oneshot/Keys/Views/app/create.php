<?php
/**
 * App: Create API Key
 */
?>

<div class="card bg-base-200 rounded-xl max-w-2xl">
    <form method="post" action="<?= route_to('app.keys') ?>" id="keys-form">
        <?= csrf_field() ?>
        <div class="card-body gap-4">

            <!-- Name -->
            <div class="grid grid-cols-1 sm:grid-cols-[12rem_1fr] items-center gap-x-6 gap-y-1">
                <span class="text-sm opacity-60"><?= __('keys.name', 'Name') ?></span>
                <input type="text" name="name" value="<?= esc(old('name', '')) ?>"
                       class="input input-sm input-bordered" required>
            </div>

            <!-- Expire -->
            <div class="grid grid-cols-1 sm:grid-cols-[12rem_1fr] items-start gap-x-6 gap-y-1">
                <span class="text-sm opacity-60 pt-1"><?= __('keys.expires_at', 'Expires At') ?></span>
                <div class="flex flex-col gap-2">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="expire_type" value="never" class="radio radio-sm" checked
                               data-expire-toggle="none">
                        <span class="text-sm"><?= __('keys.expire_never', 'Never') ?></span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="expire_type" value="days" class="radio radio-sm"
                               data-expire-toggle="days">
                        <span class="text-sm"><?= __('keys.expire_days', 'Days from now') ?></span>
                    </label>
                    <div id="expire-days-wrap" class="hidden ml-6">
                        <input type="number" name="expire_days" min="1" value="30"
                               class="input input-sm input-bordered w-28">
                    </div>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="expire_type" value="date" class="radio radio-sm"
                               data-expire-toggle="date">
                        <span class="text-sm"><?= __('keys.expire_date', 'Specific date') ?></span>
                    </label>
                    <div id="expire-date-wrap" class="hidden ml-6">
                        <input type="date" name="expire_date" class="input input-sm input-bordered">
                        <p class="text-xs opacity-40 mt-1"><?= __('keys.expire_date_hint', 'Expires at midnight UTC on that date.') ?></p>
                    </div>
                </div>
            </div>

            <!-- Request Limits -->
            <div class="grid grid-cols-1 sm:grid-cols-[12rem_1fr] items-start gap-x-6 gap-y-1">
                <span class="text-sm opacity-60 pt-1"><?= __('keys.limits_req', 'Request Limits') ?></span>
                <div>
                    <div id="limits-requests-rows" class="flex flex-col gap-2 mb-2"></div>
                    <button type="button" class="btn btn-ghost btn-xs" data-add-limit="requests">
                        + <?= __('keys.add_window', 'Add Window') ?>
                    </button>
                </div>
            </div>

            <!-- Credit Limits -->
            <div class="grid grid-cols-1 sm:grid-cols-[12rem_1fr] items-start gap-x-6 gap-y-1">
                <span class="text-sm opacity-60 pt-1"><?= __('keys.limits_cred', 'Credit Limits') ?></span>
                <div>
                    <div id="limits-credits-rows" class="flex flex-col gap-2 mb-2"></div>
                    <button type="button" class="btn btn-ghost btn-xs" data-add-limit="credits">
                        + <?= __('keys.add_window', 'Add Window') ?>
                    </button>
                </div>
            </div>

            <?= render('Keys::app/_limit_row') ?>

            <div class="flex justify-end pt-2">
                <button type="submit" id="keys-submit" class="btn btn-primary btn-sm">
                    <?= __('keys.create', 'Create Key') ?>
                </button>
            </div>
        </div>
    </form>
</div>
