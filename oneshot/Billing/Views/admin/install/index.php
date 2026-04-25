<?php
$check = fn(bool $ok) => $ok
    ? '<span class="text-success">✓</span>'
    : '<span class="text-error">✗</span>';
$baseUrl = rtrim(base_url(), '/');
?>

<div class="max-w-3xl grid gap-6">

    <!-- Status overview -->
    <div class="card bg-base-200 shadow">
        <div class="card-body p-4 sm:p-6 gap-3">
            <h2 class="font-semibold text-base"><?= __('billing.install_status', 'Status') ?></h2>

            <div class="grid grid-cols-1 sm:grid-cols-[12rem_1fr] items-start gap-x-6 gap-y-1 text-sm">
                <span class="opacity-60"><?= __('billing.plans', 'Plans') ?></span>
                <span><?= $status['plan_count'] ?> <?= $check($status['plan_count'] > 0) ?></span>

                <span class="opacity-60"><?= __('billing.packages', 'Packages') ?></span>
                <span><?= $status['package_count'] ?> <?= $check($status['package_count'] > 0) ?></span>

                <span class="opacity-60"><?= __('billing.overdraft_mode', 'Overdraft Mode') ?></span>
                <span><code class="font-mono text-xs"><?= esc($status['overdraft_mode']) ?></code></span>

                <span class="opacity-60">Stripe API key</span>
                <span><?= $check($status['stripe']['api_key']) ?> <?= $status['stripe']['api_key'] ? 'configured' : 'not set' ?></span>

                <span class="opacity-60">Stripe webhook secret</span>
                <span><?= $check($status['stripe']['webhook_secret']) ?> <?= $status['stripe']['webhook_secret'] ? 'configured' : 'not set' ?></span>

                <span class="opacity-60">Coinbase API key</span>
                <span><?= $check($status['coinbase']['api_key']) ?> <?= $status['coinbase']['api_key'] ? 'configured' : 'not set' ?></span>

                <?php if (!empty($status['provider_sync'])): ?>
                    <?php foreach ($status['provider_sync'] as $providerName => $providerData):
                        $total = 0; $synced = 0; $missing = 0; $outdated = 0;
                        foreach ($providerData['plans'] as $planRow) {
                            foreach ($planRow['prices'] as $pr) {
                                $total++;
                                if ($pr['status'] === 'ok')       $synced++;
                                elseif ($pr['status'] === 'outdated') $outdated++;
                                else                              $missing++;
                            }
                        }
                        $allOk = $total > 0 && $synced === $total;
                    ?>
                <span class="opacity-60"><?= esc(ucfirst($providerName)) ?> <?= __('billing.sync_status', 'Sync Status') ?></span>
                <span class="flex items-center gap-2">
                    <?php if ($total === 0): ?>
                        <span class="opacity-40"><?= __('billing.no_prices', 'No prices') ?></span>
                    <?php elseif ($allOk): ?>
                        <?= $check(true) ?> <span><?= __('billing.sync_all_ok', 'All prices synced') ?></span>
                    <?php else: ?>
                        <?= $check(false) ?>
                        <span>
                            <?php if ($missing > 0): ?><span class="badge badge-error badge-sm"><?= $missing ?> <?= __('billing.sync_missing', 'missing') ?></span><?php endif ?>
                            <?php if ($outdated > 0): ?><span class="badge badge-warning badge-sm"><?= $outdated ?> <?= __('billing.sync_outdated', 'outdated') ?></span><?php endif ?>
                            <?php if ($synced > 0): ?><span class="badge badge-success badge-sm"><?= $synced ?>/<?= $total ?> ok</span><?php endif ?>
                        </span>
                    <?php endif ?>
                </span>
                    <?php endforeach ?>
                <?php endif ?>
            </div>
        </div>
    </div>

    <!-- Demo data -->
    <?php if ($status['plan_count'] === 0 || $status['package_count'] === 0): ?>
    <div class="card bg-base-200 shadow">
        <div class="card-body p-4 sm:p-6 gap-3">
            <h2 class="font-semibold text-base"><?= __('billing.install_demo', 'Demo Data') ?></h2>
            <p class="text-sm opacity-70"><?= __('billing.install_demo_desc', 'Creates Starter / Pro / Business plans with monthly & yearly prices, and 3 credit packages.') ?></p>
            <form method="post" action="<?= route_to('admin.billing.install.demo') ?>">
                <?= csrf_field() ?>
                <button class="btn btn-primary btn-sm"><?= __('billing.install_demo_btn', 'Create Demo Plans & Packages') ?></button>
            </form>
        </div>
    </div>
    <?php endif ?>

    <!-- Webhook URLs -->
    <div class="card bg-base-200 shadow">
        <div class="card-body p-4 sm:p-6 gap-3">
            <h2 class="font-semibold text-base"><?= __('billing.webhook_urls', 'Webhook URLs') ?></h2>
            <p class="text-sm opacity-70"><?= __('billing.webhook_urls_desc', 'Copy these URLs into each payment provider\'s webhook settings.') ?></p>

            <?php foreach (['stripe', 'coinbase'] as $provider):
                $token = $status[$provider]['url_token'];
            ?>
            <div class="grid grid-cols-1 sm:grid-cols-[12rem_1fr] items-start gap-x-6 gap-y-1">
                <span class="text-sm opacity-60 capitalize"><?= $provider ?></span>
                <div class="flex items-center gap-2">
                    <?php if ($token): ?>
                        <code class="text-xs font-mono bg-base-300 px-2 py-1 rounded break-all"><?= $baseUrl ?>/api/v1/billing/webhook/<?= $provider ?>/<?= esc($token) ?></code>
                        <form method="post" action="<?= route_to('admin.billing.install.token') ?>" class="shrink-0">
                            <?= csrf_field() ?>
                            <input type="hidden" name="provider" value="<?= $provider ?>">
                            <button class="btn btn-xs btn-ghost opacity-60" title="Regenerate" onclick="return confirm('Regenerate token? Update the URL in <?= $provider ?> dashboard after.')">↻</button>
                        </form>
                    <?php else: ?>
                        <span class="text-xs opacity-50"><?= __('billing.token_not_set', 'Will be generated on settings save') ?></span>
                    <?php endif ?>
                </div>
            </div>
            <?php endforeach ?>
        </div>
    </div>

    <!-- Settings -->
    <div class="card bg-base-200 shadow">
        <div class="card-body p-4 sm:p-6 gap-4">
            <h2 class="font-semibold text-base"><?= __('billing.install_settings', 'Settings') ?></h2>
            <form method="post" action="<?= route_to('admin.billing.install.settings') ?>">
                <?= csrf_field() ?>

                <div class="grid gap-4">

                    <div class="grid grid-cols-1 sm:grid-cols-[12rem_1fr] items-start gap-x-6 gap-y-1">
                        <span class="text-sm opacity-60"><?= __('billing.overdraft_mode', 'Overdraft Mode') ?></span>
                        <select name="billing_overdraft_mode" class="select select-sm select-bordered">
                            <?php foreach (['deny','once','limit'] as $m): ?>
                            <option value="<?= $m ?>" <?= $status['overdraft_mode'] === $m ? 'selected' : '' ?>><?= $m ?></option>
                            <?php endforeach ?>
                        </select>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-[12rem_1fr] items-start gap-x-6 gap-y-1">
                        <span class="text-sm opacity-60"><?= __('billing.overdraft_limit', 'Overdraft Limit') ?> <span class="opacity-50 text-xs">(for limit mode)</span></span>
                        <input type="number" name="billing_overdraft_limit" value="<?= esc($status['overdraft_limit']) ?>" class="input input-sm input-bordered">
                    </div>

                    <div class="divider text-xs opacity-50 my-0"><?= __('billing.providers', 'Providers') ?></div>

                    <div class="grid grid-cols-1 sm:grid-cols-[12rem_1fr] items-start gap-x-6 gap-y-1">
                        <div>
                            <span class="text-sm opacity-60"><?= __('billing.subscription_provider', 'Subscription Provider') ?></span>
                            <p class="text-xs opacity-40 mt-1"><?= __('billing.subscription_provider_hint', 'Provider for recurring subscriptions (e.g. stripe). Comma-separate for multiple.') ?></p>
                        </div>
                        <input type="text" name="billing_subscription_provider" value="<?= esc($status['subscription_provider']) ?>" class="input input-sm input-bordered font-mono" placeholder="stripe">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-[12rem_1fr] items-start gap-x-6 gap-y-1">
                        <div>
                            <span class="text-sm opacity-60"><?= __('billing.payment_provider', 'Payment Provider') ?></span>
                            <p class="text-xs opacity-40 mt-1"><?= __('billing.payment_provider_hint', 'Provider for one-time purchases — packages and top-ups (e.g. stripe). Comma-separate for multiple.') ?></p>
                        </div>
                        <input type="text" name="billing_payment_provider" value="<?= esc($status['payment_provider']) ?>" class="input input-sm input-bordered font-mono" placeholder="stripe">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-[12rem_1fr] items-start gap-x-6 gap-y-1">
                        <div>
                            <span class="text-sm opacity-60"><?= __('billing.upgrade_collection_method', 'Upgrade Collection') ?></span>
                            <p class="text-xs opacity-40 mt-1"><?= __('billing.upgrade_collection_method_hint', 'send_invoice = user pays manually via Stripe invoice page; charge_automatically = charge card on file immediately') ?></p>
                        </div>
                        <select name="billing_upgrade_collection_method" class="select select-sm select-bordered">
                            <option value="send_invoice" <?= $status['upgrade_collection_method'] === 'send_invoice' ? 'selected' : '' ?>>send_invoice</option>
                            <option value="charge_automatically" <?= $status['upgrade_collection_method'] === 'charge_automatically' ? 'selected' : '' ?>>charge_automatically</option>
                        </select>
                    </div>

                    <div class="divider text-xs opacity-50 my-0">Stripe</div>

                    <div class="grid grid-cols-1 sm:grid-cols-[12rem_1fr] items-start gap-x-6 gap-y-1">
                        <span class="text-sm opacity-60">Secret Key</span>
                        <div>
                            <input type="password" name="billing_stripe_secret_key" value="" placeholder="<?= $status['stripe']['api_key'] ? __('billing.key_leave_blank', 'Leave blank to keep current') : 'sk_live_...' ?>" class="input input-sm input-bordered font-mono">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-[12rem_1fr] items-start gap-x-6 gap-y-1">
                        <span class="text-sm opacity-60">Publishable Key</span>
                        <input type="text" name="billing_stripe_publishable_key" value="<?= esc(option('billing.stripe_publishable_key', '')) ?>" placeholder="pk_live_..." class="input input-sm input-bordered font-mono">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-[12rem_1fr] items-start gap-x-6 gap-y-1">
                        <span class="text-sm opacity-60">Webhook Signing Secret</span>
                        <input type="password" name="billing_stripe_webhook_secret" value="" placeholder="<?= $status['stripe']['webhook_secret'] ? __('billing.key_leave_blank', 'Leave blank to keep current') : 'whsec_...' ?>" class="input input-sm input-bordered font-mono">
                    </div>

                    <div class="divider text-xs opacity-50 my-0">Coinbase</div>

                    <div class="grid grid-cols-1 sm:grid-cols-[12rem_1fr] items-start gap-x-6 gap-y-1">
                        <span class="text-sm opacity-60">API Key</span>
                        <input type="password" name="billing_coinbase_api_key" value="" placeholder="<?= $status['coinbase']['api_key'] ? __('billing.key_leave_blank', 'Leave blank to keep current') : 'Coinbase Commerce key' ?>" class="input input-sm input-bordered font-mono">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-[12rem_1fr] items-start gap-x-6 gap-y-1">
                        <span class="text-sm opacity-60">Webhook Secret</span>
                        <input type="password" name="billing_coinbase_webhook_secret" value="" placeholder="<?= $status['coinbase']['webhook_secret'] ? __('billing.key_leave_blank', 'Leave blank to keep current') : 'Shared secret' ?>" class="input input-sm input-bordered font-mono">
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="btn btn-primary btn-sm"><?= __('billing.save', 'Save Settings') ?></button>
                    </div>

                </div>
            </form>
        </div>
    </div>

    <!-- Provider sync status -->
    <?php if (!empty($status['provider_sync'])): ?>
    <div class="card bg-base-200 shadow">
        <div class="card-body p-4 sm:p-6 gap-4">
            <h2 class="font-semibold text-base"><?= __('billing.provider_sync', 'Provider Sync') ?></h2>
            <p class="text-sm opacity-70"><?= __('billing.provider_sync_desc', 'Sync local plans and prices to the payment provider. Required before accepting payments.') ?></p>

            <?php foreach ($status['provider_sync'] as $providerName => $providerData): ?>
            <div class="border border-base-300 rounded-lg p-4 gap-3 flex flex-col"
                 id="sync-block-<?= esc($providerName) ?>"
                 data-provider="<?= esc($providerName) ?>"
                 data-sync-url="<?= esc(rtrim(base_url(), '/') . '/' . ltrim(config('Prefixes')->admin, '/') . '/billing/install/sync/' . $providerName) ?>">
                <div class="flex items-center justify-between gap-4">
                    <div class="flex items-center gap-2">
                        <span class="font-mono font-semibold"><?= esc($providerName) ?></span>
                        <?php if ($providerData['api_key']): ?>
                            <span class="badge badge-success badge-sm"><?= __('billing.api_key_ok', 'API key set') ?></span>
                        <?php else: ?>
                            <span class="badge badge-error badge-sm"><?= __('billing.api_key_missing', 'no API key') ?></span>
                        <?php endif ?>
                    </div>
                    <button type="button" class="btn btn-sm btn-ghost js-sync-provider">
                        <?= __('billing.sync_all', 'Sync All') ?>
                    </button>
                </div>
                <div class="js-sync-result text-xs opacity-60 hidden"></div>

                <?php if (!empty($providerData['plans'])): ?>
                <div class="rounded border border-base-300 overflow-x-auto overflow-hidden">
                    <table class="table table-sm w-full min-w-max">
                        <thead>
                            <tr class="bg-base-200 text-xs uppercase tracking-wider opacity-70">
                                <th class="font-semibold"><?= __('billing.plan', 'Plan') ?></th>
                                <th class="font-semibold"><?= __('billing.interval', 'Interval') ?></th>
                                <th class="font-semibold"><?= __('billing.sync_status', 'Sync Status') ?></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-base-200">
                            <?php foreach ($providerData['plans'] as $planRow): ?>
                                <?php if (empty($planRow['prices'])): ?>
                                <tr class="hover:bg-base-200/50 transition-colors">
                                    <td><?= esc($planRow['name']) ?></td>
                                    <td colspan="2" class="text-sm opacity-40"><?= __('billing.no_prices', 'No prices') ?></td>
                                </tr>
                                <?php else: ?>
                                    <?php foreach ($planRow['prices'] as $i => $priceRow): ?>
                                    <tr class="hover:bg-base-200/50 transition-colors">
                                        <?php if ($i === 0): ?>
                                        <td rowspan="<?= count($planRow['prices']) ?>" class="align-top font-medium"><?= esc($planRow['name']) ?></td>
                                        <?php endif ?>
                                        <td><span class="badge badge-ghost badge-sm"><?= esc($priceRow['interval']) ?></span></td>
                                        <td>
                                            <?php if ($priceRow['status'] === 'ok'): ?>
                                                <span class="badge badge-success badge-sm"><?= __('billing.sync_ok', 'synced') ?></span>
                                            <?php elseif ($priceRow['status'] === 'outdated'): ?>
                                                <span class="badge badge-warning badge-sm"><?= __('billing.sync_outdated', 'outdated') ?></span>
                                            <?php else: ?>
                                                <span class="badge badge-error badge-sm"><?= __('billing.sync_missing', 'missing') ?></span>
                                            <?php endif ?>
                                        </td>
                                    </tr>
                                    <?php endforeach ?>
                                <?php endif ?>
                            <?php endforeach ?>
                        </tbody>
                    </table>
                </div>
                <?php endif ?>
            </div>
            <?php endforeach ?>
        </div>
    </div>
    <?php endif ?>

</div>
