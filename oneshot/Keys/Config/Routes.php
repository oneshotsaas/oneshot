<?php

$p = config('Prefixes');

$routes->group($p->app . '/keys', ['filter' => 'auth'], function ($r) {
    $r->get('/',                  '\OneShot\Keys\Controllers\App\Index::index',   ['as' => 'app.keys']);
    $r->get('create',             '\OneShot\Keys\Controllers\App\Index::create',  ['as' => 'app.keys.create']);
    $r->post('/',                 '\OneShot\Keys\Controllers\App\Index::store');
    $r->get('(:segment)',         '\OneShot\Keys\Controllers\App\Index::show/$1', ['as' => 'app.keys.show']);
    $r->get('(:segment)/edit',    '\OneShot\Keys\Controllers\App\Index::edit/$1', ['as' => 'app.keys.edit']);
    $r->post('(:segment)',        '\OneShot\Keys\Controllers\App\Index::update/$1');
    $r->post('(:segment)/toggle', '\OneShot\Keys\Controllers\App\Index::toggle/$1',  ['as' => 'app.keys.toggle']);
    $r->post('(:segment)/delete', '\OneShot\Keys\Controllers\App\Index::destroy/$1', ['as' => 'app.keys.destroy']);
});

$routes->group($p->admin . '/keys', ['filter' => 'admin'], function ($r) {
    $r->get('/', '\OneShot\Keys\Controllers\Admin\Index::index', ['as' => 'admin.keys']);
});

$routes->get($p->api . '/keys/me', '\OneShot\Keys\Controllers\Api\Keys::me', [
    'as'     => 'api.keys.me',
    'filter' => 'api',
]);

