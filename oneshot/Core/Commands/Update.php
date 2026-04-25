<?php

namespace OneShot\Core\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use OneShot\Core\Database\Seeds\SeedRunner;

class Update extends BaseCommand
{
    protected $group       = 'OneShot';
    protected $name        = 'oneshot:update';
    protected $description = 'Run all module seeders to apply new default settings (safe to re-run, never overwrites existing values)';

    public function run(array $params): void
    {
        CLI::write('Running seeders...', 'yellow');
        (new SeedRunner())->run();
        CLI::write('Done.', 'green');
    }
}
