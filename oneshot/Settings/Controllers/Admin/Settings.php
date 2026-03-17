<?php

namespace OneShot\Settings\Controllers\Admin;

use OneShot\Core\Controllers\Admin;
use OneShot\Settings\Models\Setting;

class Settings extends Admin
{
    private Setting $setting;

    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ): void {
        parent::initController($request, $response, $logger);
        $this->setting = new Setting();
    }

    public function index(): \CodeIgniter\HTTP\RedirectResponse
    {
        return redirect()->to(route_to('admin.settings.group', 'general'));
    }

    public function group(string $group): string
    {
        $groups = $this->setting->groups();

        if (! in_array($group, $groups, true)) {
            $group = $groups[0] ?? 'general';
        }

        $this->appendBC(__('settings.title', 'Settings'), route_to('admin.settings'));
        $this->appendBC(ucfirst($group));

        return $this->render('Settings::admin/index', [
            'groups'        => $groups,
            'activeGroup'   => $group,
            'fields'        => $this->setting->fields($group),
            'groupLabel'    => ucfirst($group),
        ]);
    }

    public function save(string $group): \CodeIgniter\HTTP\RedirectResponse
    {
        $fields = $this->setting->fields($group);
        $post   = $this->request->getPost();

        foreach ($fields as $field) {
            $inputName = str_replace('.', '__', $field->key);
            $value     = $post[$inputName] ?? '';

            $this->setting->store($field->key, (string) $value, null);

            // Write CSS file for custom_css fields
            if (str_ends_with($field->key, '_custom_css')) {
                // key pattern: appearance.{section}_{mode}_custom_css
                $inner = str_replace('appearance.', '', $field->key);
                // inner = admin_light_custom_css → admin_light
                $base  = str_replace('_custom_css', '', $inner);
                $parts = explode('_', $base, 2); // [section, mode]

                if (count($parts) === 2) {
                    try {
                        Setting::writeCssFile($parts[0], $parts[1], (string) $value);
                    } catch (\InvalidArgumentException) {
                        // ignore invalid section/mode
                    }
                }
            }
        }

        return $this->redirectWith(
            route_to('admin.settings.group', $group),
            __('settings.saved', 'Settings saved.')
        );
    }
}
