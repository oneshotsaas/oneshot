<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// If the app is not configured yet, lock all routes and redirect to installer.
// env('app.secretKey') is a simple in-memory $_ENV lookup — zero overhead once installed.
// Always registered — shown right after install completes (secretKey just got written)
$routes->get('install/done', '\OneShot\Install\Controllers\Install::done', ['as' => 'install.done']);

if (! env('app.secretKey')) {
    $routes->get('install',           '\OneShot\Install\Controllers\Install::index',   ['as' => 'install.index']);
    $routes->post('install/database', '\OneShot\Install\Controllers\Install::database',['as' => 'install.database']);
    $routes->get('install/app',       '\OneShot\Install\Controllers\Install::app',     ['as' => 'install.app']);
    $routes->get('install/back/db',   '\OneShot\Install\Controllers\Install::backDb',  ['as' => 'install.back-db']);
    $routes->get('install/back/app',  '\OneShot\Install\Controllers\Install::backApp', ['as' => 'install.back-app']);
    $routes->post('install/app',      '\OneShot\Install\Controllers\Install::saveApp', ['as' => 'install.save-app']);
    $routes->get('install/admin',     '\OneShot\Install\Controllers\Install::admin',   ['as' => 'install.admin']);
    $routes->post('install/finish',   '\OneShot\Install\Controllers\Install::finish',  ['as' => 'install.finish']);
    $routes->get('(.*)',              '\OneShot\Install\Controllers\Install::gate');
    $routes->post('(.*)',             '\OneShot\Install\Controllers\Install::gate');
    return; // skip all app & module routes
}

$routes->get('/', 'Home::index');
