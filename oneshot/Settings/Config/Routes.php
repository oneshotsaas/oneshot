<?php

$p = config('Prefixes');

$routes->group($p->admin . '/settings', ['filter' => 'admin'], function ($r) {
    $r->get('/',           '\OneShot\Settings\Controllers\Admin\Settings::index',       ['as' => 'admin.settings']);
    $r->post('/toggle',    '\OneShot\Settings\Controllers\Admin\Settings::toggle',      ['as' => 'admin.settings.toggle']);
    $r->get('(:segment)',  '\OneShot\Settings\Controllers\Admin\Settings::group/$1',    ['as' => 'admin.settings.group']);
    $r->post('(:segment)', '\OneShot\Settings\Controllers\Admin\Settings::save/$1');
});
