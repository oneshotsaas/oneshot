<?php
$intervals = ['month', 'quarter', 'halfyear', 'year'];
$intervalLabels = [
    'month'    => __('billing.monthly', 'Monthly'),
    'quarter'  => __('billing.quarterly', 'Quarterly'),
    'halfyear' => __('billing.halfyear', '6 Months'),
    'year'     => __('billing.yearly', 'Yearly'),
];
$intervalMonths = ['month' => 1, 'quarter' => 3, 'halfyear' => 6, 'year' => 12];

// Find which intervals are available across all plans
$availableIntervals = [];
foreach ($plans as $plan) {
    foreach ($intervals as $iv) {
        if (isset($prices[$plan->id][$iv])) {
            $availableIntervals[$iv] = true;
        }
    }
}
$activeInterval = array_key_first($availableIntervals) ?? 'month';

// Collect all active promo end timestamps for JS timers
$promoTimers = [];
foreach ($plans as $plan) {
    foreach ($intervals as $iv) {
        $ts = $promoEnds[$plan->id][$iv] ?? null;
        if ($ts && $ts !== PHP_INT_MAX) {
            $promoTimers[$plan->id . '_' . $iv] = $ts;
        }
    }
}
?>

<?php if (count($availableIntervals) > 1): ?>
<div class="flex justify-center mb-6">
    <div class="tabs tabs-boxed" id="interval-tabs">
        <?php foreach ($intervals as $iv): if (!isset($availableIntervals[$iv])) continue; ?>
        <a class="tab <?= $iv === $activeInterval ? 'tab-active' : '' ?>" onclick="switchInterval('<?= $iv ?>', this)"><?= $intervalLabels[$iv] ?></a>
        <?php endforeach ?>
    </div>
</div>
<?php endif ?>

<?php foreach ($intervals as $iv): if (!isset($availableIntervals[$iv])) continue; ?>
<div id="plans-<?= $iv ?>" class="grid grid-cols-1 md:grid-cols-3 gap-4 max-w-4xl mx-auto" style="display:<?= $iv === $activeInterval ? 'grid' : 'none' ?>">
    <?php foreach ($plans as $plan): ?>
    <?php $pr = $prices[$plan->id][$iv] ?? null; if (!$pr && !$plan->hide_price) continue; ?>
    <?php
        $promoTs       = $promoEnds[$plan->id][$iv] ?? null;
        $hasActivePromo = $pr && $pr->promo_price && $promoTs;
        $effectivePrice = $pr ? (($hasActivePromo) ? (int)$pr->promo_price : (int)$pr->price) : 0;
        $months        = $intervalMonths[$iv] ?? 1;
        // Strikethrough: explicit old_price, or computed monthly×months when interval > month and no old_price set
        $strikePrice = null;
        if ($pr) {
            if ($hasActivePromo) {
                // Promo active: strike original price
                $strikePrice = (int)$pr->price;
            } elseif ($pr->old_price) {
                $strikePrice = (int)$pr->old_price;
            } elseif ($iv !== 'month' && isset($prices[$plan->id]['month'])) {
                // No explicit old_price: show monthly × months as strikethrough
                $strikePrice = (int)$prices[$plan->id]['month']->price * $months;
            }
        }
    ?>
    <div class="card bg-base-200 shadow <?= $plan->badge ? 'border border-primary' : '' ?>">
        <div class="card-body">
            <?php if ($plan->badge): ?>
            <div class="badge badge-primary mb-1"><?= esc($plan->badge) ?></div>
            <?php endif ?>
            <h3 class="card-title"><?= esc($plan->name) ?></h3>

            <?php if ($hasActivePromo && $promoTs !== PHP_INT_MAX): ?>
            <div class="promo-timer text-xs flex items-center gap-1 text-warning font-medium"
                 data-ends="<?= $promoTs ?>"
                 data-id="timer-<?= $plan->id ?>-<?= $iv ?>">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                <span><?= __('billing.promo_ends_in', 'Offer ends in') ?></span>
                <span class="font-mono" id="timer-<?= $plan->id ?>-<?= $iv ?>">…</span>
            </div>
            <?php elseif ($hasActivePromo): ?>
            <div class="text-xs text-warning font-medium flex items-center gap-1">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                <?= __('billing.limited_offer', 'Limited offer') ?>
            </div>
            <?php endif ?>

            <div class="my-2">
                <?php if ($plan->hide_price): ?>
                    <p class="text-lg font-semibold"><?= __('billing.contact_us', 'Contact Us') ?></p>
                <?php elseif ($pr): ?>
                    <?php if ($strikePrice): ?>
                    <div class="text-sm line-through opacity-40 tabular-nums">$<?= number_format($strikePrice / 100, 2) ?></div>
                    <?php endif ?>
                    <div class="flex items-end gap-1">
                        <span class="text-3xl font-bold tabular-nums">$<?= number_format($effectivePrice / 100, 2) ?></span>
                        <span class="text-sm opacity-60 mb-1"><?= $intervalLabels[$iv] ?></span>
                    </div>
                    <?php if ($hasActivePromo && $pr->promo_discount_pct): ?>
                    <span class="badge badge-warning badge-sm"><?= sprintf(__('billing.save_pct', 'Save %d%%'), (int)$pr->promo_discount_pct) ?></span>
                    <?php elseif ($pr->discount_pct): ?>
                    <span class="badge badge-success badge-sm"><?= sprintf(__('billing.save_pct', 'Save %d%%'), (int)$pr->discount_pct) ?></span>
                    <?php endif ?>
                <?php endif ?>
            </div>

            <?php if ($plan->description): ?>
            <p class="text-sm opacity-70"><?= esc($plan->description) ?></p>
            <?php endif ?>

            <?php if ($plan->credits_included): ?>
            <p class="text-sm"><strong><?= number_format($plan->credits_included) ?></strong> <?= __('billing.credits', 'credits') ?></p>
            <?php endif ?>

            <?php if (!empty($planFeatures[$plan->id])): ?>
            <ul class="mt-2 space-y-1 text-sm">
                <?php foreach ($planFeatures[$plan->id] as $f): ?>
                <li class="flex items-center gap-2">
                    <span class="text-success">✓</span> <?= esc($f) ?>
                </li>
                <?php endforeach ?>
            </ul>
            <?php endif ?>

            <div class="card-actions mt-4">
                <?php if ($currentPlanId == $plan->id && $currentInterval === $iv): ?>
                <span class="btn btn-sm btn-disabled w-full"><?= __('billing.current', 'Current') ?></span>
                <?php elseif ($plan->hide_price): ?>
                <a href="mailto:<?= option('site.email', 'hello@example.com') ?>" class="btn btn-outline btn-sm w-full"><?= __('billing.contact_us', 'Contact Us') ?></a>
                <?php else: ?>
                <?php $label = $currentPlanId == $plan->id ? __('billing.switch_interval', 'Switch Interval') : __('billing.choose_plan', 'Choose Plan') ?>
                <a href="<?= route_to('billing.subscribe', signId($plan->id)) ?>?interval=<?= $iv ?>" class="btn btn-primary btn-sm w-full <?= $hasActivePromo ? 'btn-warning' : '' ?>"><?= $label ?></a>
                <?php endif ?>
            </div>
        </div>
    </div>
    <?php endforeach ?>
</div>
<?php endforeach ?>

<script>
function switchInterval(iv, el) {
    document.querySelectorAll('[id^="plans-"]').forEach(function(e) { e.style.display = 'none'; });
    document.getElementById('plans-' + iv).style.display = 'grid';
    document.querySelectorAll('#interval-tabs .tab').forEach(function(t) { t.classList.remove('tab-active'); });
    el.classList.add('tab-active');
}

// Countdown timers
(function() {
    var timers = <?= json_encode($promoTimers) ?>;
    if (!Object.keys(timers).length) return;

    function fmt(n) { return String(n).padStart(2, '0'); }

    function tick() {
        var now = Math.floor(Date.now() / 1000);
        for (var key in timers) {
            var el = document.getElementById('timer-' + key.replace('_', '-'));
            if (!el) continue;
            var diff = timers[key] - now;
            if (diff <= 0) {
                el.closest('.promo-timer').style.display = 'none';
                continue;
            }
            var d = Math.floor(diff / 86400);
            var h = Math.floor((diff % 86400) / 3600);
            var m = Math.floor((diff % 3600) / 60);
            var s = diff % 60;
            el.textContent = d > 0
                ? d + 'd ' + fmt(h) + ':' + fmt(m) + ':' + fmt(s)
                : fmt(h) + ':' + fmt(m) + ':' + fmt(s);
        }
    }

    tick();
    setInterval(tick, 1000);
})();
</script>
