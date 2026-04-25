<?php

namespace OneShot\Core\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class AuthSetup extends BaseCommand
{
    protected $group       = 'OneShot';
    protected $name        = 'auth:setup';
    protected $description = 'Seed Auth and Mail settings into the settings table (safe to re-run)';

    public function run(array $params): void
    {
        (new \OneShot\Auth\Database\Seeds\AuthSeeder())->run();
        CLI::write('Auth settings seeded.', 'green');
    }
}
