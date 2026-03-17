<?php

namespace OneShot\Install\Controllers;

use OneShot\Core\Controllers\Base;
use OneShot\Install\Services\Installer;

class Install extends Base
{
    protected string $layout = 'Install::layout';

    /** Guard: block access if already installed */
    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ): void {
        parent::initController($request, $response, $logger);

        $isDone = service('router')->methodName() === 'done';

        if (env('app.secretKey') && ! $isDone) {
            redirect()->to('/')->send();
            exit;
        }
    }

    /** Step 1 — DB credentials (always shows form, pre-filled from session if available) */
    public function index(): string
    {
        $checks = [
            ['label' => 'PHP ≥ 8.2',       'ok' => version_compare(PHP_VERSION, '8.2.0', '>=')],
            ['label' => 'ext-mysqli',        'ok' => extension_loaded('mysqli')],
            ['label' => 'ext-pdo',           'ok' => extension_loaded('pdo')],
            ['label' => 'ext-openssl',       'ok' => extension_loaded('openssl')],
            ['label' => 'ext-mbstring',      'ok' => extension_loaded('mbstring')],
            ['label' => 'ext-intl',          'ok' => extension_loaded('intl')],
            ['label' => 'ext-json',          'ok' => extension_loaded('json')],
            ['label' => 'writable/  write',  'ok' => is_writable(WRITEPATH)],
            ['label' => '.env  write',       'ok' => is_writable(ROOTPATH . '.env') || (! is_file(ROOTPATH . '.env') && is_writable(ROOTPATH))],
        ];

        return $this->render('Install::step_db', [
            'error'  => session()->getFlashdata('error'),
            'saved'  => session()->get('install_db') ?? [],
            'checks' => $checks,
        ]);
    }

    /** Back to step 1 */
    public function backDb(): \CodeIgniter\HTTP\RedirectResponse
    {
        return redirect()->to(route_to('install.index'));
    }

    /** Back to step 2 */
    public function backApp(): \CodeIgniter\HTTP\RedirectResponse
    {
        return redirect()->to(route_to('install.app'));
    }

    /** Catch-all redirect when not installed */
    public function gate(): \CodeIgniter\HTTP\RedirectResponse
    {
        return redirect()->to(route_to('install.index'));
    }

    /** POST Step 1: test DB, save to session, go to step 2 */
    public function database(): \CodeIgniter\HTTP\RedirectResponse
    {
        $installer = new Installer();
        $mode      = $this->request->getPost('mode');

        if ($mode === 'dsn') {
            try {
                $creds = $installer->parseDsn($this->request->getPost('dsn'));
            } catch (\Throwable $e) {
                session()->setFlashdata('error', $e->getMessage());
                return redirect()->to(route_to('install.index'));
            }
        } else {
            $driver = $this->request->getPost('driver') === 'Postgre' ? 'Postgre' : 'MySQLi';
            $creds  = [
                'hostname' => $this->request->getPost('hostname') ?: 'localhost',
                'port'     => (int) ($this->request->getPost('port') ?: ($driver === 'Postgre' ? 5432 : 3306)),
                'database' => $this->request->getPost('database'),
                'username' => $this->request->getPost('username'),
                'password' => $this->request->getPost('password'),
                'driver'   => $driver,
            ];
        }

        // Always save to session so the form stays filled on error
        session()->set('install_db', $creds);

        $error = $installer->testDb($creds);

        if ($error) {
            session()->setFlashdata('error', $error);
            return redirect()->to(route_to('install.index'));
        }

        return redirect()->to(route_to('install.app'));
    }

    /** Step 2 — application settings */
    public function app(): string|\CodeIgniter\HTTP\RedirectResponse
    {
        if (! session()->has('install_db')) {
            return redirect()->to(route_to('install.index'));
        }

        $saved = session()->get('install_app') ?? [];

        return $this->render('Install::step_app', [
            'error'      => session()->getFlashdata('error'),
            'base_url'   => $saved['base_url'] ?? (string) current_url(true)->setPath('/'),
            'app_name'   => $saved['app_name'] ?? 'OneShot',
            'env'        => $saved['environment'] ?? 'development',
            'theme_mode' => $saved['theme_mode'] ?? 'dark',
        ]);
    }

    /** POST Step 2: save app settings, go to step 3 */
    public function saveApp(): \CodeIgniter\HTTP\RedirectResponse
    {
        if (! session()->has('install_db')) {
            return redirect()->to(route_to('install.index'));
        }

        $themeMode = $this->request->getPost('theme_mode') === 'light' ? 'light' : 'dark';

        session()->set('install_app', [
            'app_name'    => trim($this->request->getPost('app_name')) ?: 'OneShot',
            'base_url'    => rtrim($this->request->getPost('base_url') ?: base_url(), '/') . '/',
            'environment' => $this->request->getPost('environment') === 'production' ? 'production' : 'development',
            'theme_mode'  => $themeMode,
        ]);

        return redirect()->to(route_to('install.admin'));
    }

    /** Step 3 — admin account */
    public function admin(): string|\CodeIgniter\HTTP\RedirectResponse
    {
        if (! session()->has('install_db') || ! session()->has('install_app')) {
            return redirect()->to(route_to('install.index'));
        }

        return $this->render('Install::step_admin', [
            'error'     => session()->getFlashdata('error'),
            'saved'     => session()->get('install_admin') ?? [],
            'timezones' => \DateTimeZone::listIdentifiers(),
        ]);
    }

    /** POST Step 3: run full install */
    public function finish(): \CodeIgniter\HTTP\RedirectResponse
    {
        if (! session()->has('install_db') || ! session()->has('install_app')) {
            return redirect()->to(route_to('install.index'));
        }

        $db    = session()->get('install_db');
        $app   = session()->get('install_app');
        $admin = [
            'name'     => $this->request->getPost('name'),
            'email'    => $this->request->getPost('email'),
            'password' => $this->request->getPost('password'),
            'timezone' => $this->request->getPost('timezone') ?: 'UTC',
        ];

        // Save before running so form stays filled on error
        session()->set('install_admin', $admin);

        $error = (new Installer())->run($db, $app['environment'], $app['app_name'], $app['base_url'], $admin, $app['theme_mode'] ?? 'dark');

        if ($error) {
            session()->setFlashdata('error', $error);
            return redirect()->to(route_to('install.admin'));
        }

        session()->remove('install_admin');

        session()->remove('install_db');
        session()->remove('install_app');
        return redirect()->to(route_to('install.done'));
    }

    /** Step 4 — success */
    public function done(): string
    {
        return $this->render('Install::done');
    }
}
