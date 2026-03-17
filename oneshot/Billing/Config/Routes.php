<?php

$p = config('Prefixes');

// App routes — authenticated users
$routes->group($p->app, ['filter' => 'auth'], function ($r) {
    $r->get('billing',                          '\OneShot\Billing\Controllers\App\Dashboard::index',        ['as' => 'billing.index']);
    $r->get('billing/plans',                    '\OneShot\Billing\Controllers\App\Plans::index',            ['as' => 'billing.plans']);
    $r->get('billing/subscribe/(:segment)',     '\OneShot\Billing\Controllers\App\Subscribe::checkout/$1',  ['as' => 'billing.subscribe']);
    $r->post('billing/subscribe/(:segment)',    '\OneShot\Billing\Controllers\App\Subscribe::store/$1');
    $r->get('billing/subscribe-success',        '\OneShot\Billing\Controllers\App\Subscribe::success',      ['as' => 'billing.subscribe.success']);
    $r->post('billing/cancel',                  '\OneShot\Billing\Controllers\App\Subscribe::cancel',       ['as' => 'billing.cancel']);
    $r->get('billing/packages',                 '\OneShot\Billing\Controllers\App\Packages::index',         ['as' => 'billing.packages']);
    $r->post('billing/packages/buy/(:segment)', '\OneShot\Billing\Controllers\App\Packages::buy/$1',        ['as' => 'billing.packages.buy']);
    $r->get('billing/usage',                    '\OneShot\Billing\Controllers\App\Usage::index',            ['as' => 'billing.usage']);
    $r->get('billing/invoices',                 '\OneShot\Billing\Controllers\App\Invoice::index',          ['as' => 'billing.invoices']);
    $r->get('billing/invoices/(:segment)',       '\OneShot\Billing\Controllers\App\Invoice::show/$1',        ['as' => 'billing.invoice']);
});

// Admin routes
$routes->group($p->admin, ['filter' => 'admin'], function ($r) {
    // Plans
    $r->get( 'billing/plans',                               '\OneShot\Billing\Controllers\Admin\Plans::index',          ['as' => 'admin.billing.plans']);
    $r->get( 'billing/plans/create',                        '\OneShot\Billing\Controllers\Admin\Plans::create',         ['as' => 'admin.billing.plans.create']);
    $r->post('billing/plans/create',                        '\OneShot\Billing\Controllers\Admin\Plans::store');
    $r->get( 'billing/plans/(:segment)',                    '\OneShot\Billing\Controllers\Admin\Plans::edit/$1',         ['as' => 'admin.billing.plan.edit']);
    $r->post('billing/plans/(:segment)',                    '\OneShot\Billing\Controllers\Admin\Plans::update/$1');
    $r->post('billing/plans/(:segment)/delete',             '\OneShot\Billing\Controllers\Admin\Plans::destroy/$1',     ['as' => 'admin.billing.plan.delete']);

    // Plan prices
    $r->get( 'billing/plans/(:segment)/prices',             '\OneShot\Billing\Controllers\Admin\PlanPrices::index/$1',  ['as' => 'admin.billing.plan.prices']);
    $r->post('billing/plans/(:segment)/prices/create',      '\OneShot\Billing\Controllers\Admin\PlanPrices::store/$1',  ['as' => 'admin.billing.plan.prices.create']);
    $r->post('billing/plans/(:segment)/prices/(:segment)',  '\OneShot\Billing\Controllers\Admin\PlanPrices::update/$1/$2', ['as' => 'admin.billing.plan.price.update']);
    $r->post('billing/plans/(:segment)/prices/(:segment)/delete', '\OneShot\Billing\Controllers\Admin\PlanPrices::destroy/$1/$2', ['as' => 'admin.billing.plan.price.delete']);

    // Promotions
    $r->get( 'billing/promotions',                          '\OneShot\Billing\Controllers\Admin\Promotions::index',     ['as' => 'admin.billing.promotions']);
    $r->get( 'billing/promotions/create',                   '\OneShot\Billing\Controllers\Admin\Promotions::create',    ['as' => 'admin.billing.promotions.create']);
    $r->post('billing/promotions/create',                   '\OneShot\Billing\Controllers\Admin\Promotions::store');
    $r->get( 'billing/promotions/(:segment)',                '\OneShot\Billing\Controllers\Admin\Promotions::edit/$1',   ['as' => 'admin.billing.promotion.edit']);
    $r->post('billing/promotions/(:segment)',                '\OneShot\Billing\Controllers\Admin\Promotions::update/$1');
    $r->post('billing/promotions/(:segment)/delete',         '\OneShot\Billing\Controllers\Admin\Promotions::destroy/$1', ['as' => 'admin.billing.promotion.delete']);

    // Packages
    $r->get( 'billing/packages',                            '\OneShot\Billing\Controllers\Admin\Packages::index',       ['as' => 'admin.billing.packages']);
    $r->get( 'billing/packages/create',                     '\OneShot\Billing\Controllers\Admin\Packages::create',      ['as' => 'admin.billing.packages.create']);
    $r->post('billing/packages/create',                     '\OneShot\Billing\Controllers\Admin\Packages::store');
    $r->get( 'billing/packages/(:segment)',                  '\OneShot\Billing\Controllers\Admin\Packages::edit/$1',     ['as' => 'admin.billing.package.edit']);
    $r->post('billing/packages/(:segment)',                  '\OneShot\Billing\Controllers\Admin\Packages::update/$1');
    $r->post('billing/packages/(:segment)/delete',           '\OneShot\Billing\Controllers\Admin\Packages::destroy/$1', ['as' => 'admin.billing.package.delete']);

    // Costs
    $r->get( 'billing/costs',                               '\OneShot\Billing\Controllers\Admin\Costs::index',          ['as' => 'admin.billing.costs']);
    $r->get( 'billing/costs/create',                        '\OneShot\Billing\Controllers\Admin\Costs::create',         ['as' => 'admin.billing.costs.create']);
    $r->post('billing/costs/create',                        '\OneShot\Billing\Controllers\Admin\Costs::store');
    $r->get( 'billing/costs/(:segment)',                     '\OneShot\Billing\Controllers\Admin\Costs::edit/$1',        ['as' => 'admin.billing.costs.edit']);
    $r->post('billing/costs/(:segment)',                     '\OneShot\Billing\Controllers\Admin\Costs::update/$1');
    $r->post('billing/costs/(:segment)/delete',              '\OneShot\Billing\Controllers\Admin\Costs::destroy/$1',    ['as' => 'admin.billing.costs.delete']);

    // Subscriptions (read-only)
    $r->get('billing/subscriptions',                        '\OneShot\Billing\Controllers\Admin\Subscriptions::index',  ['as' => 'admin.billing.subscriptions']);
    $r->get('billing/subscriptions/(:segment)',              '\OneShot\Billing\Controllers\Admin\Subscriptions::show/$1', ['as' => 'admin.billing.subscription']);

    // Transactions (read-only)
    $r->get('billing/transactions',                         '\OneShot\Billing\Controllers\Admin\Transactions::index',   ['as' => 'admin.billing.transactions']);

    // Invoices (read-only)
    $r->get('billing/invoices',                             '\OneShot\Billing\Controllers\Admin\Invoices::index',       ['as' => 'admin.billing.invoices']);
    $r->get('billing/invoices/(:segment)',                   '\OneShot\Billing\Controllers\Admin\Invoices::show/$1',    ['as' => 'admin.billing.invoice']);

    // Top-up
    $r->get( 'billing/topup/(:segment)',                    '\OneShot\Billing\Controllers\Admin\TopUp::form/$1',        ['as' => 'admin.billing.topup']);
    $r->post('billing/topup/(:segment)',                    '\OneShot\Billing\Controllers\Admin\TopUp::store/$1');

    // Setup / Installer
    $r->get( 'billing/install',         '\OneShot\Billing\Controllers\Admin\Install::index',           ['as' => 'admin.billing.install']);
    $r->post('billing/install/demo',    '\OneShot\Billing\Controllers\Admin\Install::seedDemo',        ['as' => 'admin.billing.install.demo']);
    $r->post('billing/install/settings','\OneShot\Billing\Controllers\Admin\Install::saveSettings',   ['as' => 'admin.billing.install.settings']);
    $r->post('billing/install/token',   '\OneShot\Billing\Controllers\Admin\Install::regenerateToken',['as' => 'admin.billing.install.token']);
});

// Webhook — no auth filter, token in URL
$routes->post('api/v1/billing/webhook/(:segment)/(:segment)', '\OneShot\Billing\Controllers\Admin\Webhook::handle/$1/$2', ['as' => 'billing.webhook']);
