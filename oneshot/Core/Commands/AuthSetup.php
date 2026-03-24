<?php

namespace OneShot\Core\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use OneShot\Install\Services\Installer;

class AuthSetup extends BaseCommand
{
    protected $group       = 'OneShot';
    protected $name        = 'auth:setup';
    protected $description = 'Seed Auth and Mail settings into the settings table (safe to re-run)';

    public function run(array $params): void
    {
        (new Installer())->seedAuth();
        CLI::write('Auth settings seeded.', 'green');
    }
}
