<?php

$p = config('Prefixes');

$routes->get($p->api . '/ping', '\OneShot\Core\Controllers\Api\Ping::index', ['as' => 'api.ping']);
