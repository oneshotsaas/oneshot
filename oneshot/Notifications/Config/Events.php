<?php

use CodeIgniter\Events\Events;

// When a user registers, send a system announcement notification
Events::on('user.registered', function (array $data) {
    if (empty($data['user_id'])) return;
    notify(
        (int)$data['user_id'],
        'system.announcement',
        __('notifications.type_system_announcement', 'Welcome!'),
        '',
        ['body' => __('notifications.welcome_body', 'Your account has been created.')]
    );
});
