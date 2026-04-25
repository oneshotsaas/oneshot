# Keys Module

Manages API keys for programmatic access. Keys are identified by a `key_id` (public) and verified via SHA-256 hash — the raw secret is never stored.

## Key Format

```
{prefix}{key_id}:{secret}
```

- `prefix` — configurable in Settings → API (default `oneshot_`); stored as `keys.prefix`
- `key_id` — 14 hex chars (`bin2hex(random_bytes(7))`), stored plaintext in `key_id` (UNIQUE)
- `secret` — 64 hex chars (`bin2hex(random_bytes(32))`), never stored
- Separator: `:`

Example: `oneshot_a3f1b2c4d5e6f7:abcd...64hex`

## Contexts

| Context | Routes prefix | Filter |
|---------|--------------|--------|
| App | `/app/keys` | `auth` |
| Admin | `/admin/keys` | `admin` |
| API | `/api/v1/keys` | `api-key` |

## Controllers

- `Controllers/Keys.php` — abstract base (extends App), shared `loadOwnedKey()`
- `Controllers/App/Index.php` — CRUD for user-owned keys
- `Controllers/Admin/Index.php` — read-only list of all keys (all users), extends Admin directly
- `Controllers/Api/Ping.php` — test endpoint; returns `{message: "pong"}`

## Models

- `Models/ApiKey.php` — `keys_keys` table, soft deletes
- `Models/Usage.php` — `keys_usage` table, daily usage tracking (upsert)

## Services

- `Services/KeyService.php`
  - `generate()` — creates key, returns `['raw' => ..., 'id' => ...]`
  - `validateAndTrack()` — atomic: find by key_id FOR UPDATE, verify hash, check limits, increment usage

## Filter

- `Filters/ApiKey.php` — alias `api-key` in `oneshot/Core/Config/Filters.php`
- Reads `X-API-Key` header or `Authorization: Bearer ...`
- Credit cost declared per-route: `['filter' => 'api-key:5']`
- On success sets `$_SERVER['KEYS_KEY_ID'] = $key->id`

## Tables

| Table | Purpose |
|-------|---------|
| `keys_keys` | API key registry |
| `keys_usage` | Daily usage counters (requests + credits) |

## Settings

| Key | Default | Description |
|-----|---------|-------------|
| `keys.prefix` | `oneshot_` | Prefix prepended to generated keys |

## Usage Tracking

Limits are arrays: `[{"days": 0, "max": 1000}, {"days": 7, "max": 100}]`
- `days: 0` = total all time
- `days: N` = sliding window of N days (today + N-1 prior days)

Limit check is atomic: `current_sum + pending > max` → reject before recording.
