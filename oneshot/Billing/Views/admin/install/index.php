<?php
$check = fn(bool $ok) => $ok
    ? '<span class="text-success">✓</span>'
    : '<span class="text-error">✗</span>';
$baseUrl = rtrim(base_url(), '/');
?>

<div class="max-w-3xl grid gap-6">

    <!-- Status overview -->
    <div class="card bg-base-200 shadow">
        <div class="card-body gap-3">
            <h2 class="font-semibold text-base"><?= __('billing.install_status', 'Status') ?></h2>

            <div class="grid grid-cols-[12rem_1fr] items-center gap-x-6 gap-y-2 text-sm">
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
            </div>
        </div>
    </div>

    <!-- Demo data -->
    <?php if ($status['plan_count'] === 0 || $status['package_count'] === 0): ?>
    <div class="card bg-base-200 shadow">
        <div class="card-body gap-3">
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
        <div class="card-body gap-3">
            <h2 class="font-semibold text-base"><?= __('billing.webhook_urls', 'Webhook URLs') ?></h2>
            <p class="text-sm opacity-70"><?= __('billing.webhook_urls_desc', 'Copy these URLs into each payment provider\'s webhook settings.') ?></p>

            <?php foreach (['stripe', 'coinbase'] as $provider):
                $token = $status[$provider]['url_token'];
            ?>
            <div class="grid grid-cols-[12rem_1fr] items-center gap-x-6">
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
        <div class="card-body gap-4">
            <h2 class="font-semibold text-base"><?= __('billing.install_settings', 'Settings') ?></h2>
            <form method="post" action="<?= route_to('admin.billing.install.settings') ?>">
                <?= csrf_field() ?>

                <div class="grid gap-4">

                    <div class="grid grid-cols-[12rem_1fr] items-center gap-x-6">
                        <span class="text-sm opacity-60"><?= __('billing.overdraft_mode', 'Overdraft Mode') ?></span>
                        <select name="billing_overdraft_mode" class="select select-sm select-bordered">
                            <?php foreach (['deny','once','limit'] as $m): ?>
                            <option value="<?= $m ?>" <?= $status['overdraft_mode'] === $m ? 'selected' : '' ?>><?= $m ?></option>
                            <?php endforeach ?>
                        </select>
                    </div>

                    <div class="grid grid-cols-[12rem_1fr] items-center gap-x-6">
                        <span class="text-sm opacity-60"><?= __('billing.overdraft_limit', 'Overdraft Limit') ?> <span class="opacity-50 text-xs">(for limit mode)</span></span>
                        <input type="number" name="billing_overdraft_limit" value="<?= esc($status['overdraft_limit']) ?>" class="input input-sm input-bordered">
                    </div>

                    <div class="divider text-xs opacity-50 my-0">Stripe</div>

                    <div class="grid grid-cols-[12rem_1fr] items-center gap-x-6">
                        <span class="text-sm opacity-60">Secret Key</span>
                        <input type="password" name="billing_stripe_secret_key" value="<?= $status['stripe']['api_key'] ? '••••••••' : '' ?>" placeholder="sk_live_..." class="input input-sm input-bordered font-mono">
                    </div>

                    <div class="grid grid-cols-[12rem_1fr] items-center gap-x-6">
                        <span class="text-sm opacity-60">Publishable Key</span>
                        <input type="text" name="billing_stripe_publishable_key" value="<?= esc(option('billing.stripe_publishable_key', '')) ?>" placeholder="pk_live_..." class="input input-sm input-bordered font-mono">
                    </div>

                    <div class="grid grid-cols-[12rem_1fr] items-center gap-x-6">
                        <span class="text-sm opacity-60">Webhook Signing Secret</span>
                        <input type="password" name="billing_stripe_webhook_secret" value="<?= $status['stripe']['webhook_secret'] ? '••••••••' : '' ?>" placeholder="whsec_..." class="input input-sm input-bordered font-mono">
                    </div>

                    <div class="divider text-xs opacity-50 my-0">Coinbase</div>

                    <div class="grid grid-cols-[12rem_1fr] items-center gap-x-6">
                        <span class="text-sm opacity-60">API Key</span>
                        <input type="password" name="billing_coinbase_api_key" value="<?= $status['coinbase']['api_key'] ? '••••••••' : '' ?>" placeholder="Coinbase Commerce key" class="input input-sm input-bordered font-mono">
                    </div>

                    <div class="grid grid-cols-[12rem_1fr] items-center gap-x-6">
                        <span class="text-sm opacity-60">Webhook Secret</span>
                        <input type="password" name="billing_coinbase_webhook_secret" value="<?= $status['coinbase']['webhook_secret'] ? '••••••••' : '' ?>" placeholder="Shared secret" class="input input-sm input-bordered font-mono">
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="btn btn-primary btn-sm"><?= __('billing.save', 'Save Settings') ?></button>
                    </div>

                </div>
            </form>
        </div>
    </div>

</div>
