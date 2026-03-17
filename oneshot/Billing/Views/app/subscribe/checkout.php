<div class="max-w-lg">
<form method="post">
    <?= csrf_field() ?>
    <div class="card bg-base-200 shadow">
        <div class="card-body gap-4">

            <h2><?= sprintf(__('billing.subscribe_to', 'Subscribe to %s'), esc($plan->name)) ?></h2>

            <div class="grid grid-cols-[12rem_1fr] items-center gap-x-6">
                <span class="text-sm opacity-60"><?= __('billing.billing_interval', 'Billing Interval') ?></span>
                <select name="interval" id="interval" class="select select-sm select-bordered" onchange="updateSummary()">
                    <?php
                    $intervalLabels = [
                        'month'    => __('billing.monthly',   'Monthly'),
                        'quarter'  => __('billing.quarterly', 'Quarterly'),
                        'halfyear' => __('billing.halfyear',  '6 Months'),
                        'year'     => __('billing.yearly',    'Yearly'),
                    ];
                    foreach ($prices as $pr):
                    ?>
                    <option value="<?= esc($pr->interval) ?>"
                            data-price="<?= (int)$pr->price ?>"
                            data-promo-price="<?= (int)$pr->promo_price ?>"
                            data-promo-ends="<?= esc($pr->promo_ends_at ?? '') ?>"
                            <?= $pr->interval === $selectedInterval ? 'selected' : '' ?>>
                        <?= $intervalLabels[$pr->interval] ?? $pr->interval ?> — $<?= number_format($pr->price / 100, 2) ?>
                    </option>
                    <?php endforeach ?>
                </select>
            </div>

            <div class="grid grid-cols-[12rem_1fr] items-center gap-x-6">
                <span class="text-sm opacity-60"><?= __('billing.enter_promo', 'Promo Code') ?></span>
                <input type="text" name="promo_code" value="<?= esc($promoCode ?? '') ?>" class="input input-sm input-bordered uppercase" placeholder="SAVE20">
            </div>

            <div class="bg-base-300 rounded-lg p-4 text-sm">
                <p class="font-semibold mb-3"><?= __('billing.summary', 'Order Summary') ?></p>
                <div class="flex justify-between mb-1">
                    <span><?= esc($plan->name) ?></span>
                    <span id="sum-price">—</span>
                </div>
                <div class="border-t border-base-content/10 my-2"></div>
                <div class="flex justify-between font-bold">
                    <span><?= __('billing.due_today', 'Due Today') ?></span>
                    <span id="sum-total">—</span>
                </div>
                <p class="text-xs opacity-60 mt-2"><?= number_format($plan->credits_included) ?> <?= __('billing.credits', 'credits') ?></p>
            </div>

            <button type="submit" class="btn btn-primary btn-sm"><?= __('billing.confirm_subscribe', 'Confirm Subscription') ?></button>
            <a href="<?= route_to('billing.plans') ?>" class="btn btn-ghost btn-sm"><?= __('billing.back', 'Back') ?></a>

        </div>
    </div>
</form>
</div>

<script>
function updateSummary() {
    var sel = document.getElementById('interval');
    var opt = sel.options[sel.selectedIndex];
    var price = parseInt(opt.dataset.price) || 0;
    var promoPrice = opt.dataset.promoPrice ? parseInt(opt.dataset.promoPrice) : null;
    var promoEnds  = opt.dataset.promoEnds;
    var effective  = (promoPrice && promoEnds && new Date(promoEnds) > new Date()) ? promoPrice : price;
    var fmt = function(c) { return '$' + (c / 100).toFixed(2); };
    document.getElementById('sum-price').textContent = fmt(price);
    document.getElementById('sum-total').textContent = fmt(effective);
}
updateSummary();
</script>
