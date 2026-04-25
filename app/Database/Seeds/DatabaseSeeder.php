<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Master seeder — runs all module seeders.
 * Usage: php spark db:seed DatabaseSeeder
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        (new \OneShot\Core\Database\Seeds\SeedRunner())->run();
    }
}
