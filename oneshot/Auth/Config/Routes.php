<?php

$p = config('Prefixes');

$routes->group($p->auth, function ($r) {
    $r->get('login',    '\OneShot\Auth\Controllers\Login::index',    ['as' => 'auth.login']);
    $r->post('login',   '\OneShot\Auth\Controllers\Login::store');
    $r->get('logout',   '\OneShot\Auth\Controllers\Login::destroy',  ['as' => 'auth.logout']);
    $r->get('register', '\OneShot\Auth\Controllers\Register::index', ['as' => 'auth.register']);
    $r->post('register','\OneShot\Auth\Controllers\Register::store');

    // Password reset
    $r->get('forgot',              '\OneShot\Auth\Controllers\Forgot::index', ['as' => 'auth.forgot']);
    $r->post('forgot',             '\OneShot\Auth\Controllers\Forgot::store');
    $r->get('reset/(:segment)',    '\OneShot\Auth\Controllers\Reset::index/$1',  ['as' => 'auth.reset']);
    $r->post('reset/(:segment)',   '\OneShot\Auth\Controllers\Reset::store/$1');

    // Email verification
    $r->get('verify/(:segment)',   '\OneShot\Auth\Controllers\Verify::index/$1',  ['as' => 'auth.verify']);
    $r->post('verify/resend',      '\OneShot\Auth\Controllers\Verify::resend', ['as' => 'auth.verify.resend']);

    // OAuth — secret segment prevents enumeration by people who know the framework
    $r->get('oauth/(:segment)',                              '\OneShot\Auth\Controllers\OAuth::redirect/$1',          ['as' => 'auth.oauth']);
    $r->get('oauth/(:segment)/(:segment)/callback',         '\OneShot\Auth\Controllers\OAuth::callback/$1/$2');
    $r->post('oauth/(:segment)/(:segment)/callback',        '\OneShot\Auth\Controllers\OAuth::telegram/$1/$2',       ['as' => 'auth.telegram']);
});
