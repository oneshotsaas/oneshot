<?php

namespace OneShot\Notifications\Config;

use CodeIgniter\Config\BaseConfig;

class NotificationTypes extends BaseConfig
{
    public array $types = [
        'security.login'            => ['group' => 'security', 'label' => 'New login detected',    'channels' => ['in_app', 'email'],         'defaults' => ['in_app' => true,  'email' => true],  'audience' => 'user'],
        'security.password_changed' => ['group' => 'security', 'label' => 'Password changed',      'channels' => ['in_app', 'email'],         'defaults' => ['in_app' => true,  'email' => true],  'audience' => 'user'],
        'security.email_changed'    => ['group' => 'security', 'label' => 'Email address changed', 'channels' => ['in_app', 'email'],         'defaults' => ['in_app' => true,  'email' => true],  'audience' => 'user'],
        'system.maintenance'        => ['group' => 'system',   'label' => 'Scheduled maintenance', 'channels' => ['in_app'],                  'defaults' => ['in_app' => true],                    'audience' => 'all'],
        'system.announcement'       => ['group' => 'system',   'label' => 'Product announcements', 'channels' => ['in_app', 'email'],         'defaults' => ['in_app' => true,  'email' => false], 'audience' => 'user'],
    ];

    public array $groups = [
        'security' => 'Security',
        'system'   => 'System',
    ];

    public static function register(array $types, array $groups = []): void
    {
        $instance         = config(self::class);
        $instance->types  = array_merge($instance->types,  $types);
        $instance->groups = array_merge($instance->groups, $groups);
    }
}
