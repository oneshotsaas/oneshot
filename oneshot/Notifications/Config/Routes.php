<?php

$p = config('Prefixes');

// App routes
$routes->group($p->app, ['filter' => 'auth'], function ($r) {
    $r->get( 'notifications',             '\OneShot\Notifications\Controllers\App\Inbox::index',            ['as' => 'app.notifications']);
    $r->post('notifications/read/(:num)', '\OneShot\Notifications\Controllers\App\Inbox::markRead/$1',      ['as' => 'app.notifications.read']);
    $r->post('notifications/read-all',    '\OneShot\Notifications\Controllers\App\Inbox::markAllRead',      ['as' => 'app.notifications.read_all']);
    $r->post('notifications/preference',  '\OneShot\Notifications\Controllers\App\Inbox::updatePreference', ['as' => 'app.notifications.preference']);
});

// API routes
$routes->group($p->api . '/notifications', ['filter' => 'auth'], function ($r) {
    $r->get('unread', '\OneShot\Notifications\Controllers\Api\Notifications::unread', ['as' => 'api.notifications.unread']);
});
