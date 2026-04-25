<?php

namespace OneShot\Core\Database\Seeds;

/**
 * Discovers and runs all *Seeder.php files found in module directories.
 * Scans: oneshot/{Module}/Database/Seeds/ and modules/{Module}/Database/Seeds/
 *
 * Order: oneshot/ first (library modules), modules/ second (user modules).
 * Each seeder is responsible for its own idempotency — skip existing rows,
 * only insert missing ones. Readonly (computed) values are always refreshed.
 */
class SeedRunner
{
    public function run(): void
    {
        helper('oneshot');

        foreach ($this->discover() as [$file, $class]) {
            if (! class_exists($class)) {
                require_once $file;
            }
            (new $class())->run();
        }
    }

    /** @return array<array{string, string}> list of [absolute_path, FQCN] */
    private function discover(): array
    {
        $found = [];

        foreach ($this->scanModule(ROOTPATH . 'oneshot', 'OneShot') as $item) {
            $found[] = $item;
        }

        foreach ($this->scanModule(ROOTPATH . 'modules', 'Modules') as $item) {
            $found[] = $item;
        }

        return $found;
    }

    /** Scan one root dir (oneshot/ or modules/) for *Seeder.php files */
    private function scanModule(string $root, string $rootNs): array
    {
        $found = [];

        if (! is_dir($root)) {
            return $found;
        }

        foreach (scandir($root) as $module) {
            if ($module === '.' || $module === '..') {
                continue;
            }

            $seedDir = $root . DIRECTORY_SEPARATOR . $module
                     . DIRECTORY_SEPARATOR . 'Database'
                     . DIRECTORY_SEPARATOR . 'Seeds';

            if (! is_dir($seedDir)) {
                continue;
            }

            foreach (scandir($seedDir) as $file) {
                if (! str_ends_with(strtolower($file), 'seeder.php')) {
                    continue;
                }

                $path  = $seedDir . DIRECTORY_SEPARATOR . $file;
                $class = $rootNs . '\\' . $module . '\\Database\\Seeds\\' . basename($file, '.php');

                $found[] = [$path, $class];
            }
        }

        return $found;
    }
}
