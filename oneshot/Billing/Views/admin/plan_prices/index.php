<div class="rounded-lg border border-base-300 overflow-x-auto overflow-hidden mb-6">
    <table class="table table-sm w-full min-w-max">
        <thead>
            <tr class="bg-base-200 text-xs uppercase tracking-wider opacity-70">
                <th class="font-semibold"><?= __('billing.interval', 'Interval') ?></th>
                <th class="font-semibold text-right"><?= __('billing.price', 'Price') ?></th>
                <th class="font-semibold text-right"><?= __('billing.old_price', 'Old Price') ?></th>
                <th class="font-semibold text-right"><?= __('billing.promo_price', 'Promo Price') ?></th>
                <th class="font-semibold"><?= __('billing.promo_ends_at', 'Promo Ends') ?></th>
                <th class="font-semibold"><?= __('billing.currency', 'Currency') ?></th>
                <th class="font-semibold text-right"><?= __('billing.discount_pct', 'Save %') ?></th>
                <th class="font-semibold"><?= __('billing.credits_grant', 'Credits Grant') ?></th>
                <th></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-base-200">
            <?php foreach ($prices as $pr): ?>
            <tr class="hover:bg-base-200/50 transition-colors">
                <td><span class="badge badge-ghost badge-sm"><?= esc($pr->interval) ?></span></td>
                <td class="text-right tabular-nums font-medium">$<?= $pr->price_ui ?></td>
                <td class="text-right tabular-nums opacity-50"><?= $pr->old_price ? '$' . $pr->old_price_ui : '—' ?></td>
                <td class="text-right tabular-nums opacity-70"><?= $pr->promo_price ? '$' . $pr->promo_price_ui : '<span class="opacity-40">—</span>' ?></td>
                <td class="text-sm opacity-60 whitespace-nowrap">
                    <?php if ($pr->promo_ends_at): ?>
                        <?= esc(substr($pr->promo_ends_at, 0, 16)) ?>
                    <?php elseif ($pr->promo_ends_days): ?>
                        <span class="badge badge-ghost badge-sm"><?= (int)$pr->promo_ends_days ?>d <?= __('billing.from_signup', 'from signup') ?></span>
                    <?php else: ?>
                        <span class="opacity-30">—</span>
                    <?php endif ?>
                </td>
                <td class="font-mono text-xs opacity-60"><?= esc(strtoupper($pr->currency)) ?></td>
                <td class="text-right tabular-nums"><?= $pr->discount_pct ? $pr->discount_pct . '%' : '<span class="opacity-30">—</span>' ?></td>
                <td><span class="badge badge-ghost badge-sm"><?= esc($pr->credits_grant ?? 'full') ?></span></td>
                <td>
                    <div class="flex items-center justify-end gap-1">
                        <div class="tooltip tooltip-left" data-tip="<?= __('billing.edit', 'Edit') ?>">
                            <button onclick="openPriceForm(<?= htmlspecialchars(json_encode($pr), ENT_QUOTES) ?>)" class="btn btn-ghost btn-xs btn-square">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                            </button>
                        </div>
                        <form method="post" action="<?= route_to('admin.billing.plan.price.delete', signId($plan->id), signId($pr->id)) ?>" onsubmit="return confirm('Delete?')">
                            <?= csrf_field() ?>
                            <div class="tooltip tooltip-left" data-tip="<?= __('billing.delete', 'Delete') ?>">
                                <button class="btn btn-ghost btn-xs btn-square text-base-content/30 hover:text-error hover:bg-error/10 transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                                </button>
                            </div>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endforeach ?>
        </tbody>
    </table>
</div>

<div class="card bg-base-200 shadow max-w-2xl">
    <div class="card-body p-4 sm:p-6 gap-4">
        <h3 class="font-semibold" id="price-form-title"><?= __('billing.create', 'Create') ?> <?= __('billing.price', 'Price') ?></h3>
        <form method="post" id="price-form" action="<?= route_to('admin.billing.plan.prices.create', signId($plan->id)) ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="_price_id" id="price_id" value="">
            <div class="grid gap-4">

                <div class="grid grid-cols-1 sm:grid-cols-[12rem_1fr] items-start gap-x-6 gap-y-1">
                    <span class="text-sm opacity-60"><?= __('billing.interval', 'Interval') ?></span>
                    <select name="interval" id="price_interval" class="select select-sm select-bordered">
                        <?php foreach (['month','quarter','halfyear','year'] as $iv): ?>
                        <option value="<?= $iv ?>"><?= ucfirst($iv) ?></option>
                        <?php endforeach ?>
                    </select>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-[12rem_1fr] items-start gap-x-6 gap-y-1">
                    <span class="text-sm opacity-60"><?= __('billing.currency', 'Currency') ?></span>
                    <input type="text" name="currency" id="price_currency" value="usd" class="input input-sm input-bordered" maxlength="3">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-[12rem_1fr] items-start gap-x-6 gap-y-1">
                    <span class="text-sm opacity-60"><?= __('billing.price', 'Price') ?> ($)</span>
                    <input type="text" name="price" id="price_price" value="" class="input input-sm input-bordered" required>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-[12rem_1fr] items-start gap-x-6 gap-y-1">
                    <span class="text-sm opacity-60"><?= __('billing.old_price', 'Old Price') ?> ($)</span>
                    <input type="text" name="old_price" id="price_old_price" value="" class="input input-sm input-bordered">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-[12rem_1fr] items-start gap-x-6 gap-y-1">
                    <span class="text-sm opacity-60"><?= __('billing.promo_price', 'Promo Price') ?> ($)</span>
                    <input type="text" name="promo_price" id="price_promo_price" value="" class="input input-sm input-bordered">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-[12rem_1fr] items-start gap-x-6 gap-y-1">
                    <span class="text-sm opacity-60"><?= __('billing.promo_ends_type', 'Promo End Type') ?></span>
                    <div class="flex gap-3 text-sm">
                        <label class="flex items-center gap-1 cursor-pointer">
                            <input type="radio" name="promo_ends_type" value="date" id="promo_type_date" class="radio radio-sm" onchange="togglePromoType(this.value)">
                            <?= __('billing.promo_ends_date', 'Fixed date') ?>
                        </label>
                        <label class="flex items-center gap-1 cursor-pointer">
                            <input type="radio" name="promo_ends_type" value="days" id="promo_type_days" class="radio radio-sm" onchange="togglePromoType(this.value)">
                            <?= __('billing.promo_ends_days_label', 'Days from registration') ?>
                        </label>
                        <label class="flex items-center gap-1 cursor-pointer">
                            <input type="radio" name="promo_ends_type" value="none" id="promo_type_none" class="radio radio-sm" checked onchange="togglePromoType(this.value)">
                            <?= __('billing.promo_ends_none', 'No limit') ?>
                        </label>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-[12rem_1fr] items-start gap-x-6 gap-y-1" id="promo_date_row" style="display:none">
                    <span class="text-sm opacity-60"><?= __('billing.promo_ends_at', 'Promo Ends At') ?></span>
                    <input type="datetime-local" name="promo_ends_at" id="price_promo_ends_at" value="" class="input input-sm input-bordered">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-[12rem_1fr] items-start gap-x-6 gap-y-1" id="promo_days_row" style="display:none">
                    <span class="text-sm opacity-60 pt-1"><?= __('billing.promo_ends_days', 'Days after signup') ?></span>
                    <div>
                        <input type="number" name="promo_ends_days" id="price_promo_ends_days" value="" min="1" max="365" class="input input-sm input-bordered w-28">
                        <p class="text-xs opacity-40 mt-1"><?= __('billing.promo_ends_days_hint', 'e.g. 7 = promo available for 7 days after signup') ?></p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-[12rem_1fr] items-start gap-x-6 gap-y-1">
                    <span class="text-sm opacity-60"><?= __('billing.discount_pct', 'Discount %') ?></span>
                    <input type="number" name="discount_pct" id="price_discount_pct" value="" class="input input-sm input-bordered" min="0" max="100">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-[12rem_1fr] items-start gap-x-6 gap-y-1">
                    <span class="text-sm opacity-60"><?= __('billing.promo_discount_pct', 'Promo Disc. %') ?></span>
                    <input type="number" name="promo_discount_pct" id="price_promo_discount_pct" value="" class="input input-sm input-bordered" min="0" max="100">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-[12rem_1fr] items-start gap-x-6 gap-y-1">
                    <div>
                        <span class="text-sm opacity-60"><?= __('billing.credits_grant', 'Credits Grant') ?></span>
                        <p class="text-xs opacity-40 mt-1"><?= __('billing.credits_grant_hint', 'full = all at once on subscribe/renew; monthly = one month at a time via TaskRunner') ?></p>
                    </div>
                    <select name="credits_grant" id="price_credits_grant" class="select select-sm select-bordered">
                        <option value="full"><?= __('billing.credits_grant_full', 'full — all credits upfront') ?></option>
                        <option value="monthly"><?= __('billing.credits_grant_monthly', 'monthly — one month at a time') ?></option>
                    </select>
                </div>

                <div class="flex gap-2 pt-2">
                    <button type="submit" class="btn btn-primary btn-sm"><?= __('billing.save', 'Save') ?></button>
                    <button type="button" onclick="resetPriceForm()" class="btn btn-ghost btn-sm"><?= __('billing.cancel', 'Cancel') ?></button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function togglePromoType(val) {
    document.getElementById('promo_date_row').style.display = val === 'date' ? '' : 'none';
    document.getElementById('promo_days_row').style.display = val === 'days' ? '' : 'none';
    if (val !== 'date') document.getElementById('price_promo_ends_at').value = '';
    if (val !== 'days') document.getElementById('price_promo_ends_days').value = '';
}
function openPriceForm(pr) {
    document.getElementById('price-form-title').textContent = '<?= __('billing.edit', 'Edit') ?> <?= __('billing.price', 'Price') ?>';
    document.getElementById('price_id').value = pr.hash;
    document.getElementById('price_interval').value = pr.interval;
    document.getElementById('price_currency').value = pr.currency;
    document.getElementById('price_price').value = pr.price_ui;
    document.getElementById('price_old_price').value = pr.old_price_ui || '';
    document.getElementById('price_promo_price').value = pr.promo_price_ui || '';
    document.getElementById('price_discount_pct').value = pr.discount_pct || '';
    document.getElementById('price_promo_discount_pct').value = pr.promo_discount_pct || '';
    document.getElementById('price_credits_grant').value = pr.credits_grant || 'full';
    document.getElementById('price-form').action = pr.update_url;

    if (pr.promo_ends_at) {
        document.getElementById('promo_type_date').checked = true;
        togglePromoType('date');
        document.getElementById('price_promo_ends_at').value = pr.promo_ends_at.replace(' ', 'T').substring(0, 16);
    } else if (pr.promo_ends_days) {
        document.getElementById('promo_type_days').checked = true;
        togglePromoType('days');
        document.getElementById('price_promo_ends_days').value = pr.promo_ends_days;
    } else {
        document.getElementById('promo_type_none').checked = true;
        togglePromoType('none');
    }
}
function resetPriceForm() {
    document.getElementById('price-form').reset();
    document.getElementById('price_id').value = '';
    document.getElementById('price-form-title').textContent = '<?= __('billing.create', 'Create') ?> <?= __('billing.price', 'Price') ?>';
    document.getElementById('price-form').action = '<?= route_to('admin.billing.plan.prices.create', signId($plan->id)) ?>';
    togglePromoType('none');
}
</script>
