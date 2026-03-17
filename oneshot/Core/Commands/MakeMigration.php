<?php

namespace OneShot\Core\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class MakeMigration extends BaseCommand
{
    protected $group       = 'OneShot';
    protected $name        = 'make:migration';
    protected $description = 'Create a migration file inside a module';
    protected $usage       = 'make:migration <ModuleName> [MigrationName]';
    protected $arguments   = [
        'ModuleName'    => 'The module to create the migration in',
        'MigrationName' => 'Optional migration name (default: Create{Module}Table)',
    ];

    public function run(array $params): void
    {
        $module = ucfirst(array_shift($params) ?? '');
        if (empty($module)) {
            CLI::error('Module name is required.');
            return;
        }

        $migName = array_shift($params) ?? 'Create' . $module . 'Table';
        $migName = ucfirst($migName);

        // Check both modules/ and oneshot/
        $base = null;
        foreach (['modules', 'oneshot'] as $layer) {
            $candidate = ROOTPATH . $layer . '/' . $module;
            if (is_dir($candidate)) {
                $base = $candidate;
                break;
            }
        }

        if ($base === null) {
            CLI::error('Module ' . $module . ' not found in modules/ or oneshot/.');
            return;
        }

        $dir       = $base . '/Database/Migrations';
        $timestamp = date('Y-m-d-His');
        $filename  = $dir . '/' . $timestamp . '_' . $migName . '.php';

        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $ns = is_dir(ROOTPATH . 'modules/' . $module) ? 'Modules\\' . $module : 'OneShot\\' . $module;

        file_put_contents($filename, $this->stub($ns, $migName));

        CLI::write('Migration created: ' . CLI::color(basename($filename), 'green'));
    }

    private function stub(string $ns, string $name): string
    {
        return <<<PHP
<?php

namespace $ns\Database\Migrations;

use CodeIgniter\Database\Migration;

class $name extends Migration
{
    public function up(): void
    {
        \$this->forge->addField([
            'id'         => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        \$this->forge->addPrimaryKey('id');
        \$this->forge->createTable('table_name');
    }

    public function down(): void
    {
        \$this->forge->dropTable('table_name');
    }
}
PHP;
    }
}
