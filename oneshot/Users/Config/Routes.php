<?php

$p = config('Prefixes');

$routes->group($p->admin . '/users', ['filter' => 'admin'], function ($r) {
    $r->get('/',          '\OneShot\Users\Controllers\Users::index', ['as' => 'admin.users']);
    $r->get('(:segment)', '\OneShot\Users\Controllers\Users::show/$1');
});

$routes->group($p->app . '/profile', ['filter' => 'auth'], function ($r) {
    $r->get('/',  '\OneShot\Users\Controllers\Profile::index',  ['as' => 'app.profile']);
    $r->post('/', '\OneShot\Users\Controllers\Profile::update');
});
