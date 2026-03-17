<div class="max-w-md mx-auto text-center py-10">
    <div class="text-5xl mb-4">🎉</div>
    <h2 class="text-2xl font-bold mb-2"><?= __('billing.subscribe_success', 'Subscription activated!') ?></h2>
    <p class="opacity-70 mb-6"><?= __('billing.credits_included', 'Credits added to your account') ?>.</p>
    <a href="<?= route_to('billing.index') ?>" class="btn btn-primary"><?= __('billing.dashboard', 'Go to Billing') ?></a>
</div>
