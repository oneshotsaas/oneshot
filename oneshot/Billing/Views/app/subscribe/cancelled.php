<div class="flex flex-col items-center justify-center py-20 gap-6 text-center">
    <div class="text-5xl opacity-30">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
    </div>
    <div>
        <p class="text-lg font-semibold"><?= __('billing.checkout_cancelled', 'Checkout cancelled') ?></p>
        <p class="text-sm opacity-60 mt-1"><?= __('billing.checkout_cancelled_desc', 'Your payment was not processed. No charges were made.') ?></p>
    </div>
    <a href="<?= route_to('billing.plans') ?>" class="btn btn-primary btn-sm"><?= __('billing.view_plans', 'View Plans') ?></a>
</div>
