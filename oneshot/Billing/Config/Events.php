<?php

use CodeIgniter\Events\Events;

Events::on('billing.invoice_paid', function (array $data) {
    if (empty($data['user_id'])) return;
    notify(
        (int)$data['user_id'],
        'billing.invoice_paid',
        __('notifications.type_billing_invoice_paid', 'Invoice paid'),
        isset($data['invoice_id']) ? route_to('billing.invoice', signId((int)$data['invoice_id'])) : '',
        ['invoice_id' => $data['invoice_id'] ?? null, 'body' => isset($data['invoice_id']) ? '#' . (int)$data['invoice_id'] : '']
    );
});

Events::on('billing.payment_failed', function (array $data) {
    if (empty($data['user_id'])) return;
    notify(
        (int)$data['user_id'],
        'billing.payment_failed',
        __('notifications.type_billing_payment_failed', 'Payment failed'),
        '',
        ['body' => $data['error'] ?? '']
    );
});

Events::on('billing.subscription_renewed', function (array $data) {
    if (empty($data['user_id'])) return;
    notify(
        (int)$data['user_id'],
        'billing.subscription_renewed',
        __('notifications.type_billing_subscription_renewed', 'Subscription renewed')
    );
});

Events::on('billing.trial_ending', function (array $data) {
    if (empty($data['user_id'])) return;
    notify(
        (int)$data['user_id'],
        'billing.trial_ending',
        __('notifications.type_billing_trial_ending', 'Trial ending soon')
    );
});
