# Install

First-run wizard that runs automatically when `app.secretKey` is absent from `.env`.

## Contexts
- Front: `/install` — 2-step setup form (no auth required, no DB needed at start)

## Flow
1. `GET /install` — Step 1: DB credentials (fields or DSN URL). Tests connection live.
2. `POST /install/database` → validates connection → stores creds in session → Step 2.
3. `GET /install/setup` — Step 2: app name, base URL, environment, admin account + timezone.
4. `POST /install/finish` → writes `.env`, runs `spark migrate --all`, creates admin row.
5. `GET /install/done` — success screen with link to login.

## Gate mechanism
`app/Config/Routes.php` checks `env('app.secretKey')` (in-memory `$_ENV` lookup).
If absent: only install routes are registered + `(.*)` catch-all redirects everything else to `/install`.
After install: check is skipped entirely — zero overhead.

## Controllers
- `Install` — wizard steps + `gate()` (catch-all redirect)

## Services
- `Installer::parseDsn($url)` — parses mysql:// or postgres:// URL into creds array
- `Installer::testDb($creds)` — tests connection (MySQLi or ext-pgsql), returns error string
- `Installer::run($db, $env, $appName, $baseUrl, $admin)` — full install pipeline

## What gets written to .env
```
CI_ENVIRONMENT = production|development
app.name       = ...
app.baseURL    = ...
app.secretKey  = <64-char random hex>
database.*     = ... (fields or DSN)
```

## Dependencies
- uses: OneShot\Auth\Database\Migrations (run via spark)
- creates: first row in `auth_users` with role=admin
