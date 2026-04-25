<div class="max-w-md mx-auto py-8">
    <div class="card bg-base-200 shadow">
        <div class="card-body p-4 sm:p-6 gap-4">
            <p class="text-sm opacity-60"><?= __('billing.select_provider_desc', 'Choose how you would like to pay.') ?></p>

            <div class="grid gap-3">
                <?php foreach ($providers as $provider): ?>
                <form method="post" action="<?= route_to('billing.packages.buy_with_provider', $packageHash, $provider) ?>">
                    <?= csrf_field() ?>
                    <?php if (!empty($promoCode)): ?>
                    <input type="hidden" name="promo_code" value="<?= esc($promoCode) ?>">
                    <?php endif ?>
                    <button type="submit" class="btn btn-outline btn-sm w-full capitalize">
                        <?= esc($provider) ?>
                    </button>
                </form>
                <?php endforeach ?>
            </div>

            <a href="<?= route_to('billing.packages') ?>" class="btn btn-ghost btn-sm w-full sm:w-auto"><?= __('billing.cancel', 'Cancel') ?></a>
        </div>
    </div>
</div>
