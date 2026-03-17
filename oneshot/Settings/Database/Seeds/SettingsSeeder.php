<?php

namespace OneShot\Settings\Database\Seeds;

/**
 * Settings seeder — inserts default settings rows.
 * Plain class (not CI4 Seeder) so it can be called from anywhere.
 */
class SettingsSeeder
{
    private static array $themes = [
        'light','dark','cupcake','bumblebee','emerald','corporate','synthwave','retro',
        'cyberpunk','valentine','halloween','garden','forest','aqua','lofi','pastel',
        'fantasy','wireframe','black','luxury','dracula','cmyk','autumn','business',
        'acid','lemonade','night','coffee','winter','dim','nord','sunset',
    ];

    public function run(): void
    {
        $themeOptions = json_encode(
            array_map(fn($t) => ['value' => $t, 'label' => ucfirst($t)], self::$themes)
        );

        $modeOptions = json_encode([
            ['value' => 'light', 'label' => 'Light'],
            ['value' => 'dark',  'label' => 'Dark'],
        ]);

        $rows = [
            // General
            ['key' => 'general.app_name',        'type' => 'text',   'value' => 'OneShot', 'label' => 'App Name',              'sort' => 1],
            ['key' => 'general.default_timezone', 'type' => 'select', 'value' => 'UTC',     'label' => 'Default Timezone',      'sort' => 2,
             'options' => json_encode(array_map(fn($tz) => ['value' => $tz, 'label' => $tz], \DateTimeZone::listIdentifiers()))],
            ['key' => 'general.default_lang',     'type' => 'select', 'value' => 'en',      'label' => 'Default Language',      'sort' => 3,
             'options' => json_encode([['value'=>'en','label'=>'English']])],

            // Branding
            ['key' => 'branding.logo_url',    'type' => 'url', 'value' => '', 'label' => 'Logo URL',    'sort' => 1],
            ['key' => 'branding.favicon_url', 'type' => 'url', 'value' => '', 'label' => 'Favicon URL', 'sort' => 2],

            // Appearance — admin
            ['key' => 'appearance.admin_default_mode',   'type' => 'select', 'value' => 'dark',  'label' => 'Admin — default mode',       'sort' => 10, 'options' => $modeOptions],
            ['key' => 'appearance.admin_light_theme',    'type' => 'select', 'value' => 'light', 'label' => 'Admin — light theme',         'sort' => 11, 'options' => $themeOptions],
            ['key' => 'appearance.admin_dark_theme',     'type' => 'select', 'value' => 'dark',  'label' => 'Admin — dark theme',          'sort' => 12, 'options' => $themeOptions],
            ['key' => 'appearance.admin_light_custom_css','type' => 'code',  'value' => '', 'label' => 'Admin — light custom CSS',  'sort' => 13],
            ['key' => 'appearance.admin_dark_custom_css', 'type' => 'code',  'value' => '', 'label' => 'Admin — dark custom CSS',   'sort' => 14],

            // Appearance — app
            ['key' => 'appearance.app_default_mode',   'type' => 'select', 'value' => 'dark',  'label' => 'App — default mode',       'sort' => 20, 'options' => $modeOptions],
            ['key' => 'appearance.app_light_theme',    'type' => 'select', 'value' => 'light', 'label' => 'App — light theme',         'sort' => 21, 'options' => $themeOptions],
            ['key' => 'appearance.app_dark_theme',     'type' => 'select', 'value' => 'dark',  'label' => 'App — dark theme',          'sort' => 22, 'options' => $themeOptions],
            ['key' => 'appearance.app_light_custom_css','type' => 'code',  'value' => '', 'label' => 'App — light custom CSS',  'sort' => 23],
            ['key' => 'appearance.app_dark_custom_css', 'type' => 'code',  'value' => '', 'label' => 'App — dark custom CSS',   'sort' => 24],

            // Appearance — front
            ['key' => 'appearance.front_default_mode',   'type' => 'select', 'value' => 'dark',  'label' => 'Front — default mode',       'sort' => 30, 'options' => $modeOptions],
            ['key' => 'appearance.front_light_theme',    'type' => 'select', 'value' => 'light', 'label' => 'Front — light theme',         'sort' => 31, 'options' => $themeOptions],
            ['key' => 'appearance.front_dark_theme',     'type' => 'select', 'value' => 'dark',  'label' => 'Front — dark theme',          'sort' => 32, 'options' => $themeOptions],
            ['key' => 'appearance.front_light_custom_css','type' => 'code',  'value' => '', 'label' => 'Front — light custom CSS',  'sort' => 33],
            ['key' => 'appearance.front_dark_custom_css', 'type' => 'code',  'value' => '', 'label' => 'Front — dark custom CSS',   'sort' => 34],
        ];

        $model = new \OneShot\Settings\Models\Setting();

        foreach ($rows as $row) {
            $existing = $model->where('key', $row['key'])->where('user_id', null)->limit(1)->first();
            if ($existing) {
                continue;
            }

            $row['user_id'] = null;
            if (!isset($row['options'])) {
                $row['options'] = null;
            }

            // Encrypt the value before insert
            $model->store($row['key'], $row['value'], null);

            // Update label/type/options/sort separately
            $justInserted = $model->where('key', $row['key'])->where('user_id', null)->limit(1)->first();
            if ($justInserted) {
                $model->update($justInserted->id, [
                    'type'    => $row['type'],
                    'options' => $row['options'] ?? null,
                    'label'   => $row['label'],
                    'sort'    => $row['sort'],
                ]);
            }
        }

        // Write empty CSS files so they exist from the start
        foreach (['admin', 'app', 'front'] as $section) {
            foreach (['light', 'dark'] as $mode) {
                \OneShot\Settings\Models\Setting::writeCssFile($section, $mode, '');
            }
        }
    }
}
