<?php

namespace Config;

use CodeIgniter\Config\AutoloadConfig;

/**
 * -------------------------------------------------------------------
 * AUTOLOADER CONFIGURATION
 * -------------------------------------------------------------------
 *
 * This file defines the namespaces and class maps so the Autoloader
 * can find the files as needed.
 *
 * NOTE: If you use an identical key in $psr4 or $classmap, then
 *       the values in this file will overwrite the framework's values.
 *
 * NOTE: This class is required prior to Autoloader instantiation,
 *       and does not extend BaseConfig.
 */
class Autoload extends AutoloadConfig
{
    /**
     * -------------------------------------------------------------------
     * Namespaces
     * -------------------------------------------------------------------
     * This maps the locations of any namespaces in your application to
     * their location on the file system. These are used by the autoloader
     * to locate files the first time they have been instantiated.
     *
     * The 'Config' (APPPATH . 'Config') and 'CodeIgniter' (SYSTEMPATH) are
     * already mapped for you.
     *
     * You may change the name of the 'App' namespace if you wish,
     * but this should be done prior to creating any namespaced classes,
     * else you will need to modify all of those classes for this to work.
     *
     * @var array<string, list<string>|string>
     */
    public $psr4 = [
        APP_NAMESPACE    => APPPATH,
        'OneShot\\Core'  => ROOTPATH . 'oneshot/Core',
    ];

    public function __construct()
    {
        parent::__construct();

        // Register modules/{Module} and oneshot/{Module} (except Core, mapped above) as their
        // own PSR-4 namespaces here — at Autoload construction time — rather than at runtime via
        // a pre_system event listener. pre_system only fires for full HTTP/CLI app boots; spark
        // commands like `routes`, `migrate`, and `make:migration` build the Autoloader/routes
        // directly and never fire it, so namespaces registered there are invisible to those
        // commands. Registering here makes every module visible everywhere, always.
        foreach (glob(ROOTPATH . 'modules/*', GLOB_ONLYDIR) as $dir) {
            $this->psr4['Modules\\' . basename($dir)] = $dir;
        }

        foreach (glob(ROOTPATH . 'oneshot/*', GLOB_ONLYDIR) as $dir) {
            $name = basename($dir);
            if ($name !== 'Core') {
                $this->psr4['OneShot\\' . $name] = $dir;
            }
        }

        foreach (glob(ROOTPATH . 'providers/*', GLOB_ONLYDIR) as $dir) {
            $this->psr4['Providers\\' . basename($dir)] = $dir;
        }
    }

    /**
     * -------------------------------------------------------------------
     * Class Map
     * -------------------------------------------------------------------
     * The class map provides a map of class names and their exact
     * location on the drive. Classes loaded in this manner will have
     * slightly faster performance because they will not have to be
     * searched for within one or more directories as they would if they
     * were being autoloaded through a namespace.
     *
     * Prototype:
     *   $classmap = [
     *       'MyClass'   => '/path/to/class/file.php'
     *   ];
     *
     * @var array<string, string>
     */
    public $classmap = [];

    /**
     * -------------------------------------------------------------------
     * Files
     * -------------------------------------------------------------------
     * The files array provides a list of paths to __non-class__ files
     * that will be autoloaded. This can be useful for bootstrap operations
     * or for loading functions.
     *
     * Prototype:
     *   $files = [
     *       '/path/to/my/file.php',
     *   ];
     *
     * @var list<string>
     */
    public $files = [];

    /**
     * -------------------------------------------------------------------
     * Helpers
     * -------------------------------------------------------------------
     * Prototype:
     *   $helpers = [
     *       'form',
     *   ];
     *
     * @var list<string>
     */
    public $helpers = [];
}
