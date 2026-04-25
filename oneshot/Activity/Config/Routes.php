<?php

$p = config('Prefixes');

$routes->group($p->admin . '/activity', ['filter' => 'admin'], function ($r) {
    $r->get('/', '\OneShot\Activity\Controllers\Admin\Activity::index', ['as' => 'admin.activity']);
});
