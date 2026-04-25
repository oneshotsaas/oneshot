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
            $secretKey = $this->seal();
            // Patch the running process so seeders get correct DB + encryption key.
            // The process bootstrapped before .env was written, so both are stale.
            $this->refreshEnvironment($secretKey);
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
    private function seal(): string
    {
        $key = bin2hex(random_bytes(32));
        file_put_contents(ROOTPATH . '.env', "app.secretKey = {$key}\n", FILE_APPEND);
        return $key;
    }

    /**
     * Patch the running process environment after writing .env.
     *
     * The PHP process loaded .env at bootstrap — before writeEnv() created it.
     * So $_ENV has no DB credentials and no secretKey.
     * We fix both here so seeders run with the correct connection and encryption key.
     */
    private function refreshEnvironment(string $secretKey): void
    {
        // Encryption key
        putenv("app.secretKey={$secretKey}");
        $_ENV['app.secretKey'] = $secretKey;

        // DB credentials — patch CI4's Database config so models use real connection
        $cfg = config('Database');
        $cfg->default = array_merge($cfg->default, [
            'hostname' => $this->creds['hostname'] ?? 'localhost',
            'database' => $this->creds['database'] ?? '',
            'username' => $this->creds['username'] ?? '',
            'password' => $this->creds['password'] ?? '',
            'port'     => (int) ($this->creds['port'] ?? 3306),
            'DBDriver' => $this->creds['driver']   ?? 'MySQLi',
            'DBPrefix' => '',
            'charset'  => 'utf8mb4',
            'DBCollat' => 'utf8mb4_unicode_ci',
        ]);
    }

    private function seedSettings(string $appName, string $themeMode): void
    {
        try {
            (new \OneShot\Core\Database\Seeds\SeedRunner())->run();

            // Apply install-time choices on top of seeder defaults
            $model = new \OneShot\Settings\Models\Setting();
            $model->store('general.app_name', $appName, null);
            foreach (['admin', 'app', 'front'] as $section) {
                $model->store("appearance.{$section}_default_mode", $themeMode, null);
            }
        } catch (\Throwable $e) {
            log_message('error', 'Seeder failed during install: ' . $e->getMessage()
                . ' in ' . $e->getFile() . ':' . $e->getLine());
        }
    }

    public function seedAuth(): void
    {
        (new \OneShot\Auth\Database\Seeds\AuthSeeder())->run();
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
            "INSERT INTO auth_users (email, password, name, role, lang, timezone, status, email_verified_at, created_at, updated_at)
             VALUES (?, ?, ?, 'admin', 'en', ?, 'active', ?, ?, ?)"
        );

        $stmt->bind_param('sssssss',
            $admin['email'], $hash, $admin['name'], $admin['timezone'], $now, $now, $now
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
            "INSERT INTO auth_users (email, password, name, role, lang, timezone, status, email_verified_at, created_at, updated_at)
             VALUES ($1, $2, $3, 'admin', 'en', $4, 'active', $5, $6, $7)",
            [$admin['email'], $hash, $admin['name'], $admin['timezone'], $now, $now, $now]
        );

        if (! $result) {
            throw new \RuntimeException('Failed to create admin: ' . pg_last_error($conn));
        }

        pg_close($conn);
    }
}
