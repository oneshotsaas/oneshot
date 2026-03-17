<?php

namespace OneShot\Core\Config;

use CodeIgniter\Config\Filters as BaseFilters;

class Filters extends BaseFilters
{
    public array $aliases = [
        'auth'  => \OneShot\Core\Filters\Auth::class,
        'admin' => \OneShot\Core\Filters\Admin::class,
        'api'   => \OneShot\Core\Filters\ApiFilter::class,
    ];

    // No global URI rules — filters applied on route groups only
    public array $globals  = [];
    public array $methods  = [];
    public array $filters  = [];
}
