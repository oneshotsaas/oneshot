<?php

$p = config('Prefixes');

$routes->group($p->app, ['filter' => 'auth'], function ($r) {
    $r->get('/', '\Modules\Dashboard\Controllers\Dashboard::index', ['as' => 'app.index']);
});

$routes->group($p->admin, ['filter' => 'admin'], function ($r) {
    $r->get('/', '\Modules\Dashboard\Controllers\Admin::index', ['as' => 'admin.index']);
});
