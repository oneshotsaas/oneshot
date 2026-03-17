<?php

$p = config('Prefixes');

$routes->group($p->auth, function ($r) {
    $r->get('login',    '\OneShot\Auth\Controllers\Login::index',    ['as' => 'auth.login']);
    $r->post('login',   '\OneShot\Auth\Controllers\Login::store');
    $r->get('logout',   '\OneShot\Auth\Controllers\Login::destroy',  ['as' => 'auth.logout']);
    $r->get('register', '\OneShot\Auth\Controllers\Register::index', ['as' => 'auth.register']);
    $r->post('register','\OneShot\Auth\Controllers\Register::store');
});
