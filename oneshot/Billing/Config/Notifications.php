<?php

return [
    'groups' => [
        'billing' => 'Billing',
    ],
    'types' => [
        'billing.invoice_paid'            => ['group' => 'billing', 'label' => 'Invoice paid',                      'channels' => ['in_app', 'email', 'telegram'], 'defaults' => ['in_app' => true,  'email' => true,  'telegram' => false], 'audience' => 'user'],
        'billing.payment_failed'          => ['group' => 'billing', 'label' => 'Payment failed',                    'channels' => ['in_app', 'email', 'telegram'], 'defaults' => ['in_app' => true,  'email' => true,  'telegram' => true],  'audience' => 'user'],
        'billing.subscription_renewed'    => ['group' => 'billing', 'label' => 'Subscription renewed',              'channels' => ['in_app', 'email'],             'defaults' => ['in_app' => true,  'email' => false],                     'audience' => 'user'],
        'billing.subscription_upgraded'   => ['group' => 'billing', 'label' => 'Subscription upgraded',             'channels' => ['in_app', 'email'],             'defaults' => ['in_app' => true,  'email' => true],                      'audience' => 'user'],
        'billing.subscription_canceled'   => ['group' => 'billing', 'label' => 'Subscription canceled',             'channels' => ['in_app', 'email'],             'defaults' => ['in_app' => true,  'email' => true],                      'audience' => 'user'],
        'billing.package_purchased'       => ['group' => 'billing', 'label' => 'Package purchased',                 'channels' => ['in_app', 'email'],             'defaults' => ['in_app' => true,  'email' => false],                     'audience' => 'user'],
        'billing.refund_issued'           => ['group' => 'billing', 'label' => 'Refund issued',                     'channels' => ['in_app', 'email'],             'defaults' => ['in_app' => true,  'email' => true],                      'audience' => 'user'],
        'billing.trial_ending'            => ['group' => 'billing', 'label' => 'Trial ending soon',                 'channels' => ['in_app', 'email'],             'defaults' => ['in_app' => true,  'email' => true],                      'audience' => 'user'],
        'billing.provider_sync_required'  => ['group' => 'billing', 'label' => 'Payment provider sync required',   'channels' => ['in_app'],                      'defaults' => ['in_app' => true],                                         'audience' => 'admin'],
    ],
];
