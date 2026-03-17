<?php

namespace OneShot\Core;

class Loader
{
    public static function boot(): void
    {
        $loader = service('autoloader');

        // modules/ — first (priority over oneshot/)
        foreach (glob(ROOTPATH . 'modules/*', GLOB_ONLYDIR) as $dir) {
            $loader->addNamespace('Modules\\' . basename($dir), $dir);
        }

        // oneshot/ — all except Core (already registered)
        foreach (glob(ROOTPATH . 'oneshot/*', GLOB_ONLYDIR) as $dir) {
            $name = basename($dir);
            if ($name !== 'Core') {
                $loader->addNamespace('OneShot\\' . $name, $dir);
            }
        }

        // providers/ — contract implementations
        foreach (glob(ROOTPATH . 'providers/*', GLOB_ONLYDIR) as $dir) {
            $loader->addNamespace('Providers\\' . basename($dir), $dir);
        }

        // helpers — oneshot core and all modules
        foreach (glob(ROOTPATH . 'oneshot/*/Helpers/*.php') as $file) {
            require_once $file;
        }

        foreach (glob(ROOTPATH . 'modules/*/Helpers/*.php') as $file) {
            require_once $file;
        }
    }
}
