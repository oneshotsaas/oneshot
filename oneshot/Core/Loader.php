<?php

namespace OneShot\Core;

class Loader
{
    public static function boot(): void
    {
        // Namespace registration for modules/*, oneshot/* and providers/* now happens
        // statically in app/Config/Autoload.php (constructor), so it's visible to spark
        // commands too, not just full app boots. This method only handles things that
        // must run per-request/per-process: helper file loading and notification type
        // registration.

        // helpers — oneshot core and all modules
        foreach (glob(ROOTPATH . 'oneshot/*/Helpers/*.php') as $file) {
            require_once $file;
        }

        foreach (glob(ROOTPATH . 'modules/*/Helpers/*.php') as $file) {
            require_once $file;
        }

        // Notification type registration — declarative, no side effects
        foreach (glob(ROOTPATH . 'oneshot/*/Config/Notifications.php') as $file) {
            if (str_contains($file, DIRECTORY_SEPARATOR . 'Notifications' . DIRECTORY_SEPARATOR)) continue;
            $def = require $file;
            \OneShot\Notifications\Config\NotificationTypes::register($def['types'] ?? [], $def['groups'] ?? []);
        }
        foreach (glob(ROOTPATH . 'modules/*/Config/Notifications.php') as $file) {
            $def = require $file;
            \OneShot\Notifications\Config\NotificationTypes::register($def['types'] ?? [], $def['groups'] ?? []);
        }
    }
}
