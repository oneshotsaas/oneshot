<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Prefixes extends BaseConfig
{
    public string $front = '';
    public string $auth  = 'auth';
    public string $app   = 'app';
    public string $admin = 'admin';
    public string $api   = 'api/v1';
}
