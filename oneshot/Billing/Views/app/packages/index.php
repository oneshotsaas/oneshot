<div class="grid grid-cols-1 md:grid-cols-3 gap-4 max-w-3xl">
    <?php foreach ($packages as $pkg): ?>
    <div class="card bg-base-200 shadow <?= $pkg->badge ? 'border border-primary' : '' ?>">
        <div class="card-body">
            <?php if ($pkg->badge): ?>
            <div class="badge badge-primary mb-1"><?= esc($pkg->badge) ?></div>
            <?php endif ?>
            <h3 class="card-title"><?= esc($pkg->name) ?></h3>
            <p class="text-3xl font-bold my-1"><?= number_format($pkg->credits) ?> <span class="text-base font-normal opacity-70"><?= __('billing.credits', 'credits') ?></span></p>
            <div>
                <?php if ($pkg->old_price): ?>
                <span class="text-sm line-through opacity-40">$<?= number_format($pkg->old_price / 100, 2) ?></span>
                <?php endif ?>
                <span class="text-xl font-bold">$<?= number_format($pkg->price / 100, 2) ?></span>
            </div>
            <div class="card-actions mt-4">
                <form method="post" action="<?= route_to('billing.packages.buy', signId($pkg->id)) ?>">
                    <?= csrf_field() ?>
                    <div class="form-control mb-2">
                        <input type="text" name="promo_code" placeholder="<?= __('billing.enter_promo', 'Promo code') ?>" class="input input-bordered input-sm uppercase">
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm w-full"><?= __('billing.buy', 'Buy') ?></button>
                </form>
            </div>
        </div>
    </div>
    <?php endforeach ?>
    <?php if (empty($packages)): ?>
    <p class="opacity-60 col-span-3">—</p>
    <?php endif ?>
</div>
