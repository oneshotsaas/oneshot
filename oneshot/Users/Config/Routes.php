<?php

$p = config('Prefixes');

$routes->group($p->admin . '/users', ['filter' => 'admin'], function ($r) {
    $r->get('/',          '\OneShot\Users\Controllers\Admin\Users::index', ['as' => 'admin.users']);
    $r->get('(:segment)', '\OneShot\Users\Controllers\Admin\Users::show/$1');
});

$routes->group($p->app . '/profile', ['filter' => 'auth'], function ($r) {
    $r->get('/',  '\OneShot\Users\Controllers\App\Profile::index',  ['as' => 'app.profile']);
    $r->post('/', '\OneShot\Users\Controllers\App\Profile::update');
});

$routes->group($p->api . '/users', ['filter' => 'api'], function ($r) {
    $r->get('/',               '\OneShot\Users\Controllers\Api\Users::index');
    $r->post('/',              '\OneShot\Users\Controllers\Api\Users::store');
    $r->get('(:num)',          '\OneShot\Users\Controllers\Api\Users::show/$1');
    $r->post('(:num)',         '\OneShot\Users\Controllers\Api\Users::update/$1');
    $r->post('(:num)/delete',  '\OneShot\Users\Controllers\Api\Users::delete/$1');
});
