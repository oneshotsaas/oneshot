# Settings Module

Core module for storing and retrieving application settings (global) and user preferences (per-user).

## Migration
- `oneshot/Core/Database/Migrations/2026-03-15-000000_CreateSettingsTable.php`
- All migrations live in `oneshot/Core/Database/Migrations/` — never in module subdirectories

## Table: `settings`

| Column  | Type         | Description |
|---------|--------------|-------------|
| key     | VARCHAR(128) | `group.param` e.g. `appearance.admin_dark_theme` |
| value   | TEXT         | AES-256-CTR encrypted at rest |
| type    | VARCHAR(20)  | `text`, `password`, `textarea`, `select`, `multiselect`, `color`, `url`, `code`, `boolean`, `readonly` |
| options | TEXT (JSON)  | `[{"value":"dark","label":"Dark"}, ...]` for select types |
| user_id | INT NULL     | NULL = global; user ID = per-user override |
| label   | VARCHAR(128) | Human-readable label for the settings UI |
| sort    | TINYINT      | Display order within group |

## Helpers

```php
option('appearance.admin_dark_theme', 'dark')      // Global setting
userOption('appearance.mode', 'dark')               // Per-user (resolves from session)
userOption('appearance.mode', 'dark', $userId)      // Per-user explicit ID
setOption('appearance.mode', 'light', $userId)      // Save per-user
setOption('general.app_name', 'MyApp')              // Save global (userId = null)
```

## Model API

```php
$model = new \OneShot\Settings\Models\Setting();
$model->fetch('key', 'default', $userId)   // get one
$model->store('key', 'value', $userId)     // upsert (encrypts)
$model->preload(int $userId)              // preload all user rows into cache
$model->fields('appearance')              // all rows for a group (for forms)
$model->groups()                          // distinct group names
Setting::writeCssFile('admin', 'dark', $css) // write theme CSS file
```

## Custom CSS Files

Saved to `public/assets/css/themes/custom-{section}-{mode}.css`.
Linked automatically from `_head.php` if the file is non-empty.

Sections: `admin`, `app`, `front`
Modes: `light`, `dark`

## Translatable Settings (Labels & Hints)

Settings registered by any module can have translatable labels and hints. The admin UI resolves them automatically — no extra code needed.

**Label** — resolved via `__($field->key, $field->label)`:
- Setting key `billing.overdraft_mode` → looks up `overdraft_mode` in `billing.php`
- If not found, falls back to the `label` column in the DB row, then to the raw key

**Hint** — resolved via `__($field->key . '_hint', '')`:
- Setting key `billing.overdraft_mode` → looks up `overdraft_mode_hint` in `billing.php`
- If the key is missing or empty, no hint is rendered

**Convention** — when a module registers settings, add matching entries to its language file:
```php
// oneshot/Billing/Language/en/billing.php
'overdraft_mode'      => 'Overdraft Mode',
'overdraft_mode_hint' => 'deny — block; once — allow going negative once; limit — allow down to floor.',
```

## Field Types

| Type        | Renders as | Notes |
|-------------|------------|-------|
| `text`      | text input | |
| `password`  | password input | show/hide + copy buttons |
| `textarea`  | textarea | |
| `code`      | monospace textarea | spellcheck off |
| `select`    | `<select>` | requires `options` JSON |
| `multiselect` | `<select multiple>` | requires `options` JSON, value stored as JSON array |
| `url`       | url input | `https://` placeholder |
| `color`     | color picker | |
| `boolean`   | toggle | stores `"0"` / `"1"` |
| `json`      | custom UI (group-specific) | **never saved via generic form submit** — updated via `POST /admin/settings/toggle` AJAX endpoint; value is a JSON object of `"field.key": true/false` pairs |
| `readonly`  | copyable text input | **never saved on form submit** — value must be set in seeder; re-seed when base URL or secrets change |

## AJAX Toggle Endpoint

`POST /admin/settings/toggle` — atomically flips a single key in a `type=json` setting.

```js
fetch('/admin/settings/toggle', {
    method: 'POST',
    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/x-www-form-urlencoded' },
    body: new URLSearchParams({ [csrfName]: csrfHash, setting: 'notifications.defaults', field: 'billing.invoice_paid.email', enabled: '1' })
})
```

Returns `{ ok: true, csrf_hash: "..." }`. The caller must update its stored CSRF hash from the response.

Rules:
- Only works for settings with `type='json'`; returns 422 if the row is any other type
- Creates the row if it doesn't exist yet (fetch defaults to `'{}'`)
- AJAX only — returns 400 if not an AJAX request

## Default Groups

- **general** — app_name, default_timezone, default_lang
- **appearance** — themes, default modes, custom CSS per section+mode, logo, favicon
- **notifications** — `notifications.defaults` (type=json, admin-level channel defaults), `notifications.queue_mode` (type=boolean, async delivery toggle)
