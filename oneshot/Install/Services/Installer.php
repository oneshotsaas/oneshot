<?php

namespace OneShot\Install\Services;

class Installer
{
    private array $creds = [];

    /**
     * Parse a DSN URL into a credentials array.
     * Supports: mysql://, postgres://, postgresql://
     */
    public function parseDsn(string $dsn): array
    {
        $parsed = parse_url($dsn);

        if ($parsed === false || empty($parsed['host'])) {
            throw new \InvalidArgumentException('Invalid connection URL.');
        }

        $scheme = strtolower($parsed['scheme'] ?? 'mysql');
        $driver = str_starts_with($scheme, 'post') ? 'Postgre' : 'MySQLi';

        return [
            'hostname' => $parsed['host'],
            'port'     => $parsed['port'] ?? ($driver === 'Postgre' ? 5432 : 3306),
            'database' => ltrim($parsed['path'] ?? '', '/'),
            'username' => urldecode($parsed['user'] ?? ''),
            'password' => urldecode($parsed['pass'] ?? ''),
            'driver'   => $driver,
            'dsn'      => $dsn,
        ];
    }

    /**
     * Test DB connection. Returns error string or '' on success.
     */
    public function testDb(array $creds): string
    {
        return ($creds['driver'] ?? 'MySQLi') === 'Postgre'
            ? $this->testPgsql($creds)
            : $this->testMysql($creds);
    }

    private function testMysql(array $creds): string
    {
        try {
            $conn = new \mysqli(
                $creds['hostname'],
                $creds['username'],
                $creds['password'],
                '',
                (int) ($creds['port'] ?? 3306)
            );
            $conn->close();
            return '';
        } catch (\mysqli_sql_exception $e) {
            return $e->getMessage();
        }
    }

    private function testPgsql(array $creds): string
    {
        if (! function_exists('pg_connect')) {
            return 'PHP ext-pgsql is not installed.';
        }

        // Try target DB first, fall back to system 'postgres' — only verifying credentials
        $conn = @pg_connect(sprintf(
            "host=%s port=%d dbname=%s user=%s password=%s connect_timeout=5",
            $creds['hostname'], (int) ($creds['port'] ?? 5432),
            $creds['database'], $creds['username'], $creds['password']
        ));

        if (! $conn) {
            $conn = @pg_connect(sprintf(
                "host=%s port=%d dbname=postgres user=%s password=%s connect_timeout=5",
                $creds['hostname'], (int) ($creds['port'] ?? 5432),
                $creds['username'], $creds['password']
            ));

            if (! $conn) {
                return 'PostgreSQL connection failed. Check credentials.';
            }
        }

        pg_close($conn);
        return '';
    }

    private function createDatabaseMysql(array $creds): void
    {
        try {
            $conn = new \mysqli(
                $creds['hostname'], $creds['username'], $creds['password'],
                '', (int) ($creds['port'] ?? 3306)
            );
        } catch (\mysqli_sql_exception $e) {
            throw new \RuntimeException('Cannot connect to MySQL: ' . $e->getMessage());
        }

        $db = $conn->real_escape_string($creds['database']);

        if (! $conn->query("CREATE DATABASE IF NOT EXISTS `{$db}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci")) {
            $error = $conn->error;
            $conn->close();
            throw new \RuntimeException("Cannot create database: {$error}");
        }

        $conn->close();
    }

    private function createDatabasePgsql(array $creds): void
    {
        // Try target DB — maybe it already exists
        $conn = @pg_connect(sprintf(
            "host=%s port=%d dbname=%s user=%s password=%s connect_timeout=5",
            $creds['hostname'], (int) ($creds['port'] ?? 5432),
            $creds['database'], $creds['username'], $creds['password']
        ));

        if ($conn) {
            pg_close($conn);
            return;
        }

        // DB doesn't exist — connect to system db and create it
        $conn = pg_connect(sprintf(
            "host=%s port=%d dbname=postgres user=%s password=%s",
            $creds['hostname'], (int) ($creds['port'] ?? 5432),
            $creds['username'], $creds['password']
        ));

        if (! $conn) {
            throw new \RuntimeException('Cannot connect to PostgreSQL.');
        }

        $db = pg_escape_identifier($conn, $creds['database']);

        if (! pg_query($conn, "CREATE DATABASE {$db}")) {
            $error = pg_last_error($conn);
            pg_close($conn);
            throw new \RuntimeException("Cannot create database: {$error}");
        }

        pg_close($conn);
    }

    /**
     * Full install: write .env → migrate → create admin.
     * Returns '' on success or error message.
     */
    public function run(array $db, string $env, string $appName, string $baseUrl, array $admin, string $themeMode = 'dark'): string
    {
        $this->creds = $db;

        try {
            if (($db['driver'] ?? 'MySQLi') === 'Postgre') {
                $this->createDatabasePgsql($db);
            } else {
                $this->createDatabaseMysql($db);
            }
            // Write .env WITHOUT app.secretKey — installer stays accessible if next steps fail
            $this->writeEnv($db, $env, $appName, $baseUrl);
            $this->migrate();
            $this->createAdmin($db, $admin);
            // All done — seal the installation by writing app.secretKey last
            $this->seal();
            $this->seedSettings($appName, $themeMode);
            return '';
        } catch (\Throwable $e) {
            return $e->getMessage();
        }
    }

    private function writeEnv(array $db, string $env, string $appName, string $baseUrl): void
    {
        $driver = $db['driver'] ?? 'MySQLi';

        $lines = [
            "CI_ENVIRONMENT = {$env}",
            "",
            "app.name    = {$appName}",
            "app.baseURL = {$baseUrl}",
            "",
        ];

        if (! empty($db['dsn'])) {
            $lines[] = "database.default.DSN      = {$db['dsn']}";
            $lines[] = "database.default.DBDriver = {$driver}";
        } else {
            $lines[] = "database.default.hostname = {$db['hostname']}";
            $lines[] = "database.default.database = {$db['database']}";
            $lines[] = "database.default.username = {$db['username']}";
            $lines[] = "database.default.password = {$db['password']}";
            $lines[] = "database.default.DBDriver = {$driver}";
            $lines[] = "database.default.port     = {$db['port']}";
        }

        file_put_contents(ROOTPATH . '.env', implode("\n", $lines) . "\n");
    }

    /** Append app.secretKey as the very last step — signals installation is complete */
    private function seal(): void
    {
        $key = bin2hex(random_bytes(32));
        file_put_contents(ROOTPATH . '.env', "app.secretKey = {$key}\n", FILE_APPEND);
    }

    private function seedSettings(string $appName, string $themeMode): void
    {
        try {
            (new \OneShot\Settings\Database\Seeds\SettingsSeeder())->run();

            $model = new \OneShot\Settings\Models\Setting();
            $model->store('general.app_name', $appName, null);

            foreach (['admin', 'app', 'front'] as $section) {
                $model->store("appearance.{$section}_default_mode", $themeMode, null);
                // Both light and dark themes default to their matching DaisyUI theme names
                // The seeder already did this, but ensure mode preference is respected
            }
            $this->seedAuthSettings($model);
        } catch (\Throwable $e) {
            // Non-fatal — log and continue
            log_message('error', 'Settings seeder failed: ' . $e->getMessage());
        }
    }

    private function resolveReadonlyValue(string $key): string
    {
        // auth.oauth_{provider}_callback → callback URL with secret
        if (preg_match('/^auth\.oauth_(\w+)_callback$/', $key, $m)) {
            return \OneShot\Auth\Services\OAuthService::callbackUrl($m[1]);
        }
        return '';
    }

    public function seedAuth(): void
    {
        helper('oneshot');
        $this->seedAuthSettings(new \OneShot\Settings\Models\Setting());
    }

    private function seedAuthSettings(\OneShot\Settings\Models\Setting $model): void
    {
        $rows = [
            // Mail / SMTP
            ['key' => 'mail.smtp_host',   'type' => 'text',     'label' => 'SMTP Host',       'sort' => 1, 'options' => null],
            ['key' => 'mail.smtp_port',   'type' => 'text',     'label' => 'SMTP Port',       'sort' => 2, 'options' => null],
            ['key' => 'mail.smtp_user',   'type' => 'text',     'label' => 'SMTP Username',   'sort' => 3, 'options' => null],
            ['key' => 'mail.smtp_pass',   'type' => 'password', 'label' => 'SMTP Password',   'sort' => 4, 'options' => null],
            ['key' => 'mail.smtp_crypto', 'type' => 'select',   'label' => 'Encryption',      'sort' => 5,
                'options' => json_encode([['value'=>'tls','label'=>'TLS (port 587)'],['value'=>'ssl','label'=>'SSL (port 465)'],['value'=>'none','label'=>'None']])],
            ['key' => 'mail.from_email',  'type' => 'text',     'label' => 'From Email',      'sort' => 6, 'options' => null],
            ['key' => 'mail.from_name',   'type' => 'text',     'label' => 'From Name',       'sort' => 7, 'options' => null],

            // Auth — behavior
            ['key' => 'auth.email_verification', 'type' => 'select', 'label' => 'Email Verification', 'sort' => 1,
                'options' => json_encode([
                    ['value'=>'required', 'label'=>'Required (Recommended)'],
                    ['value'=>'optional', 'label'=>'Optional'],
                    ['value'=>'disabled', 'label'=>'Disabled'],
                ])],
            ['key' => 'auth.password_min_length',        'type' => 'text',    'label' => '', 'sort' => 2, 'options' => null],
            ['key' => 'auth.password_require_uppercase', 'type' => 'boolean', 'label' => '', 'sort' => 3, 'options' => null],
            ['key' => 'auth.password_require_numbers',   'type' => 'boolean', 'label' => '', 'sort' => 4, 'options' => null],
            ['key' => 'auth.password_require_symbols',   'type' => 'boolean', 'label' => '', 'sort' => 5, 'options' => null],

            // Google
            ['key' => 'auth.oauth_google_enabled',    'type' => 'boolean',  'label' => '', 'sort' => 10, 'options' => null],
            ['key' => 'auth.oauth_google_callback',   'type' => 'readonly', 'label' => '', 'sort' => 11, 'options' => null],
            ['key' => 'auth.oauth_google_id',         'type' => 'text',     'label' => '', 'sort' => 12, 'options' => null],
            ['key' => 'auth.oauth_google_secret',     'type' => 'password', 'label' => '', 'sort' => 13, 'options' => null],
            // Facebook
            ['key' => 'auth.oauth_facebook_enabled',  'type' => 'boolean',  'label' => '', 'sort' => 20, 'options' => null],
            ['key' => 'auth.oauth_facebook_callback', 'type' => 'readonly', 'label' => '', 'sort' => 21, 'options' => null],
            ['key' => 'auth.oauth_facebook_id',       'type' => 'text',     'label' => '', 'sort' => 22, 'options' => null],
            ['key' => 'auth.oauth_facebook_secret',   'type' => 'password', 'label' => '', 'sort' => 23, 'options' => null],
            // GitHub
            ['key' => 'auth.oauth_github_enabled',    'type' => 'boolean',  'label' => '', 'sort' => 30, 'options' => null],
            ['key' => 'auth.oauth_github_callback',   'type' => 'readonly', 'label' => '', 'sort' => 31, 'options' => null],
            ['key' => 'auth.oauth_github_id',         'type' => 'text',     'label' => '', 'sort' => 32, 'options' => null],
            ['key' => 'auth.oauth_github_secret',     'type' => 'password', 'label' => '', 'sort' => 33, 'options' => null],
            // Apple
            ['key' => 'auth.oauth_apple_enabled',     'type' => 'boolean',  'label' => '', 'sort' => 40, 'options' => null],
            ['key' => 'auth.oauth_apple_callback',    'type' => 'readonly', 'label' => '', 'sort' => 41, 'options' => null],
            ['key' => 'auth.oauth_apple_id',          'type' => 'text',     'label' => '', 'sort' => 42, 'options' => null],
            ['key' => 'auth.oauth_apple_team_id',     'type' => 'text',     'label' => '', 'sort' => 43, 'options' => null],
            ['key' => 'auth.oauth_apple_key_id',      'type' => 'text',     'label' => '', 'sort' => 44, 'options' => null],
            ['key' => 'auth.oauth_apple_secret',      'type' => 'password', 'label' => '', 'sort' => 45, 'options' => null],
            // LinkedIn
            ['key' => 'auth.oauth_linkedin_enabled',  'type' => 'boolean',  'label' => '', 'sort' => 50, 'options' => null],
            ['key' => 'auth.oauth_linkedin_callback', 'type' => 'readonly', 'label' => '', 'sort' => 51, 'options' => null],
            ['key' => 'auth.oauth_linkedin_id',       'type' => 'text',     'label' => '', 'sort' => 52, 'options' => null],
            ['key' => 'auth.oauth_linkedin_secret',   'type' => 'password', 'label' => '', 'sort' => 53, 'options' => null],
            // Microsoft
            ['key' => 'auth.oauth_microsoft_enabled', 'type' => 'boolean',  'label' => '', 'sort' => 60, 'options' => null],
            ['key' => 'auth.oauth_microsoft_callback','type' => 'readonly', 'label' => '', 'sort' => 61, 'options' => null],
            ['key' => 'auth.oauth_microsoft_id',      'type' => 'text',     'label' => '', 'sort' => 62, 'options' => null],
            ['key' => 'auth.oauth_microsoft_secret',  'type' => 'password', 'label' => '', 'sort' => 63, 'options' => null],
            // Telegram
            ['key' => 'auth.oauth_telegram_enabled',  'type' => 'boolean',  'label' => '', 'sort' => 70, 'options' => null],
            ['key' => 'auth.oauth_telegram_callback', 'type' => 'readonly', 'label' => '', 'sort' => 71, 'options' => null],
            ['key' => 'auth.oauth_telegram_bot_token','type' => 'password', 'label' => '', 'sort' => 72, 'options' => null],
            ['key' => 'auth.oauth_telegram_bot_name', 'type' => 'text',     'label' => '', 'sort' => 73, 'options' => null],
        ];

        $defaults = [
            'mail.smtp_port'             => '587',
            'mail.smtp_crypto'           => 'tls',
            'auth.email_verification'    => 'required',
            'auth.password_min_length'   => '8',
        ];

        $db = \Config\Database::connect();

        foreach ($rows as $row) {
            $existing = $db->table('settings')
                           ->where('key', $row['key'])
                           ->where('user_id IS NULL', null, false)
                           ->get()->getRowObject();

            if ($existing) {
                $update = [
                    'sort'    => $row['sort'],
                    'type'    => $row['type'],
                    'options' => $row['options'],
                ];
                // readonly fields store a computed value — always refresh (base_url or secret may change)
                if ($row['type'] === 'readonly') {
                    $update['value'] = encrypt($this->resolveReadonlyValue($row['key']));
                }
                $db->table('settings')->where('id', $existing->id)->update($update);
                continue;
            }

            $value = $row['type'] === 'readonly'
                ? $this->resolveReadonlyValue($row['key'])
                : ($defaults[$row['key']] ?? '');

            $db->table('settings')->insert([
                'key'     => $row['key'],
                'value'   => encrypt($value),
                'type'    => $row['type'],
                'options' => $row['options'],
                'label'   => $row['label'],
                'sort'    => $row['sort'],
                'user_id' => null,
            ]);
        }
    }

    private function migrate(): void
    {
        // Run in-process via CI4 MigrationRunner.
        // The current process loaded .env before DB config was written,
        // so we pass credentials directly to db_connect() bypassing config.
        $db = db_connect([
            'DSN'      => $this->creds['dsn'] ?? '',
            'hostname' => $this->creds['hostname'] ?? '',
            'database' => $this->creds['database'] ?? '',
            'username' => $this->creds['username'] ?? '',
            'password' => $this->creds['password'] ?? '',
            'port'     => (int) ($this->creds['port'] ?? 3306),
            'DBDriver' => $this->creds['driver'] ?? 'MySQLi',
            'DBPrefix' => '',
            'charset'  => 'utf8mb4',
            'DBCollat' => 'utf8mb4_unicode_ci',
        ], false);

        $runner = \Config\Services::migrations(null, $db);

        try {
            $runner->setNamespace(null)->latest();
        } catch (\Throwable $e) {
            throw new \RuntimeException('Migration failed: ' . $e->getMessage());
        }
    }

    private function createAdmin(array $db, array $admin): void
    {
        $driver = $db['driver'] ?? 'MySQLi';
        $now    = date('Y-m-d H:i:s');
        $hash   = password_hash($admin['password'], PASSWORD_DEFAULT);

        if ($driver === 'Postgre') {
            $this->createAdminPgsql($db, $admin, $hash, $now);
        } else {
            $this->createAdminMysql($db, $admin, $hash, $now);
        }
    }

    private function createAdminMysql(array $db, array $admin, string $hash, string $now): void
    {
        try {
            $conn = new \mysqli(
                $db['hostname'], $db['username'], $db['password'],
                $db['database'], (int) ($db['port'] ?? 3306)
            );
        } catch (\mysqli_sql_exception $e) {
            throw new \RuntimeException('Cannot reconnect to MySQL: ' . $e->getMessage());
        }

        $stmt = $conn->prepare(
            "INSERT INTO auth_users (email, password, name, role, lang, timezone, status, created_at, updated_at)
             VALUES (?, ?, ?, 'admin', 'en', ?, 'active', ?, ?)"
        );

        $stmt->bind_param('ssssss',
            $admin['email'], $hash, $admin['name'], $admin['timezone'], $now, $now
        );
        $stmt->execute();

        if ($stmt->errno) {
            throw new \RuntimeException('Failed to create admin: ' . $stmt->error);
        }

        $conn->close();
    }

    private function createAdminPgsql(array $db, array $admin, string $hash, string $now): void
    {
        $conn = pg_connect(sprintf(
            "host=%s port=%d dbname=%s user=%s password=%s",
            $db['hostname'], (int) ($db['port'] ?? 5432),
            $db['database'], $db['username'], $db['password']
        ));

        if (! $conn) {
            throw new \RuntimeException('Cannot reconnect to PostgreSQL.');
        }

        $result = pg_query_params($conn,
            "INSERT INTO auth_users (email, password, name, role, lang, timezone, status, created_at, updated_at)
             VALUES ($1, $2, $3, 'admin', 'en', $4, 'active', $5, $6)",
            [$admin['email'], $hash, $admin['name'], $admin['timezone'], $now, $now]
        );

        if (! $result) {
            throw new \RuntimeException('Failed to create admin: ' . pg_last_error($conn));
        }

        pg_close($conn);
    }
}
