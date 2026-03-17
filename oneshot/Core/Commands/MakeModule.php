<?php

namespace OneShot\Core\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class MakeModule extends BaseCommand
{
    protected $group       = 'OneShot';
    protected $name        = 'make:module';
    protected $description = 'Create a new module scaffold in modules/';
    protected $usage       = 'make:module <ModuleName>';
    protected $arguments   = ['ModuleName' => 'The name of the module (PascalCase)'];

    public function run(array $params): void
    {
        $name = array_shift($params);

        if (empty($name)) {
            CLI::error('Module name is required.');
            return;
        }

        $name = ucfirst($name);
        $base = ROOTPATH . 'modules/' . $name;
        $ns   = 'Modules\\' . $name;
        $low  = strtolower($name);

        $dirs = [
            $base . '/Config',
            $base . '/Controllers',
            $base . '/Models',
            $base . '/Services',
            $base . '/Views/front',
            $base . '/Views/admin',
            $base . '/Views/app',
            $base . '/Database/Migrations',
        ];

        foreach ($dirs as $dir) {
            if (! is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
        }

        $this->writeFile($base . '/Config/Routes.php', $this->stubRoutes($ns, $low));
        $this->writeFile($base . '/Controllers/' . $name . '.php', $this->stubController($ns, $name));
        $this->writeFile($base . '/' . $low . '.md', $this->stubMd($name));

        CLI::write('Module ' . CLI::color($name, 'green') . ' created at modules/' . $name . '/');
    }

    private function writeFile(string $path, string $content): void
    {
        if (! file_exists($path)) {
            file_put_contents($path, $content);
        }
    }

    private function stubRoutes(string $ns, string $low): string
    {
        return <<<PHP
<?php

\$p = config('Prefixes');

\$routes->group(\$p->front . '/' . '$low', function (\$r) {
    \$r->get('/', '\\$ns\Controllers\\$low' . '::index', ['as' => '$low.index']);
});
PHP;
    }

    private function stubController(string $ns, string $name): string
    {
        return <<<PHP
<?php

namespace $ns\Controllers;

use OneShot\Core\Controllers\Front;

class $name extends Front
{
    public function index(): string
    {
        return \$this->render('$name::front/index');
    }
}
PHP;
    }

    private function stubMd(string $name): string
    {
        return <<<MD
# $name

One or two sentences describing the purpose of this module.

## Contexts
- Front: /prefix/...  — what it does

## Controllers
- `$name` — base controller

## Models
- (none yet)

## Services
- (none yet)

## Events
- (none yet)

## Dependencies
- (none yet)
MD;
    }
}
