# Activity Module

Admin journal of system events. Auto-logs every event dispatched via the Event Bus — no manual instrumentation needed.

## Contexts
- **Admin** — `/admin/activity` — filterable event log table

## Controllers
- `Admin/Activity` — index() with GET filters: user_id, action, date_from, date_to

## Models
- `Log extends Base` — table `activity_logs`
  - `user_id INT NULL` — NULL = system/CLI action
  - `action VARCHAR(100)` — event name (e.g. `billing.invoice_paid`)
  - `subject_type VARCHAR(100) NULL`, `subject_id INT NULL` — optional entity reference
  - `metadata TEXT NULL` — JSON payload (scalar values only)
  - `ip VARCHAR(45) NULL`
  - `created_at DATETIME NULL`
  - No soft deletes, no updated_at

## Services
- `Activity extends Base`
  - `log(?int $userId, string $action, ?string $subjectType, ?int $subjectId, array $metadata, ?string $ip)`

## Helpers
- `activity(string $action, ?string $subjectType, ?int $subjectId, array $metadata, ?int $userId, ?string $ip)` — global helper

## Config
- `Config/Events.php` — auto-discovered by CI4; calls `Dispatcher::listenAny()` to auto-log all events
  - Guard: `if (is_cli()) return;` — no session/request in CLI context

## Auto-logging Behaviour

Every call to `event()` automatically creates an `activity_logs` row. No extra code needed in domain modules.

Payload is filtered to scalar values only before storage — complex objects and nested arrays are stripped.

## Dependencies
- `OneShot\Core\Services\Dispatcher` — `listenAny()` hook
- `clientIp()` helper — real IP detection behind proxies

## Migrations
- `2026-04-04-100003_CreateActivityLogsTable` — `activity_logs`
