# Notifications Module

Multi-channel notification delivery (in-app, email, Telegram) with 3-tier preference system and async task queue delivery.

## Contexts
- **App** — `/app/notifications` inbox, mark-read, AJAX preference toggles
- **Api** — `/api/v1/notifications/unread` — badge polling

## Controllers
- `App/Notifications` (abstract) — base, injects `Notifier`
- `App/Inbox` — index, markRead, markAllRead, updatePreference
- `Api/Notifications` — unread()

## Models
- `Notification` — table `notifications` (user_id, type, title, url, data JSON, read_at, created_at)

## Services
- `Notifier extends Base`
  - `notify(int $userId, string $type, string $title, string $url, array $data)` — sends via all enabled channels
  - `getUnread(int $userId, int $limit)` — for bell dropdown
  - `countUnread(int $userId)` — badge count
  - `markRead(int $userId, int $id)`
  - `markAllRead(int $userId)`
  - `getPreferences(int $userId)` — returns `[typeKey => [channel => bool]]` merged with admin/code defaults; reads from `settings` (key `notifications.prefs`, user_id=$userId)
  - `updatePreference(int $userId, string $type, string $channel, bool $enabled)` — stores user override into `settings` (key `notifications.prefs`, user_id=$userId) as JSON

## Channels
- `InApp` — inserts row into `notifications` table directly
- `Email` — enqueues `notification.email` task; if `notifications.queue_mode=0` runs synchronously via `TaskRunner::runOne()`
- `Telegram` — enqueues `notification.telegram` task; same queue mode logic

## Preference Tiers (3-level, evaluated in order)
1. **User** — row in `notification_preferences` for this user+type+channel
2. **Admin** — `notifications.defaults` JSON setting (key: `"type.channel"`, value: bool)
3. **Code** — `defaults` array in `NotificationTypes` config

## Config
- `NotificationTypes` (extends BaseConfig) — registry of types and groups
  - `register(array $types, array $groups)` — called by Loader for each module's `Config/Notifications.php`
  - Type definition: `['group'=>'billing', 'label'=>'...', 'channels'=>['in_app','email'], 'defaults'=>['in_app'=>true,'email'=>true]]`
- `Config/Events.php` — auto-discovered by CI4; registers `user.registered` listener

## Module Notification Registration

Each domain module creates `Config/Notifications.php` returning a plain array (no side effects):

```php
// oneshot/Billing/Config/Notifications.php
return [
    'groups' => ['billing' => 'Billing'],
    'types'  => [
        'billing.invoice_paid' => ['group'=>'billing', 'label'=>'Invoice paid', 'channels'=>['in_app','email','telegram'], 'defaults'=>['in_app'=>true,'email'=>true,'telegram'=>false]],
    ],
];
```

`Loader::boot()` calls `NotificationTypes::register()` for every such file.

## Layouts
- `layouts/_bell.php` — topbar dropdown; rendered in `Core::layouts/app.php`

## JS
- `public/assets/notifications/notifications.js` — polls `/api/v1/notifications/unread` every 30s; handles bell badge, dropdown list, profile page preference toggles (`.notif-pref-toggle`)

## Events
Listens: `user.registered` → sends system.announcement welcome notification.
Emits: nothing directly.

## Notify Service

`service('notify', $channel)` in `app/Config/Services.php` returns `\OneShot\Core\Contracts\Notify`.

- `service('notify', 'telegram')` → `Providers\Telegram\Telegram`
- `service('notify', 'email')` → `Providers\Email\Email` (wraps `MailService`)
- `service('notify')` (no channel) → reads `notifications.notify_provider` setting, falls back to `telegram`

### Notify contract: `send(string|object $to, string $message, array $options = []): bool`

`$to` accepts either a **user object** or a **direct string** value:
- User object — provider extracts the right field automatically (`$user->telegram_id` for Telegram, `$user->email` for Email)
- String — used as-is (chat_id for Telegram, email address for Email)

```php
// user object — provider picks the right field
service('notify')->send($user, 'Your invoice is paid.');

// direct value — explicit
service('notify', 'telegram')->send('123456789', 'Hello');
service('notify', 'email')->send('user@example.com', 'Hello', ['subject' => 'Subject']);
```

`TaskRunner` uses `service('notify', ...)` for both `notification.telegram` and `notification.email` tasks, passing string identifiers from the task payload.

To add a new push provider: implement `OneShot\Core\Contracts\Notify` with `send(string|object $to, ...)`, place in `providers/Name/Name.php`, add a `match` arm in `Services::notify()`, add the option to the `notifications.notify_provider` and `notifications.channels` settings.

## Dependencies
- `OneShot\Core\Services\TaskRunner` — async task delivery
- `OneShot\Core\Models\Task` — task table
- `OneShot\Settings\Models\Setting` — `notifications.defaults`, `notifications.queue_mode`, `notifications.notify_provider`, `notifications.channels`
- `OneShot\Auth\Models\User` — email, telegram_id for channel delivery
- `Providers\Telegram\Telegram` — Telegram delivery via `service('notify', 'telegram')`
- `Providers\Email\Email` — Email delivery via `service('notify', 'email')`

## Migrations
- `2026-04-04-100002_CreateNotificationsTable` — `notifications`
- `2026-04-04-100004_AddTelegramSupport` — seeds `notifications.defaults` (json), `notifications.queue_mode` (boolean) settings rows
- `2026-04-18-100000_AddNotificationProviderSettings` — seeds `notifications.notify_provider` (select), `notifications.channels` (multiselect)
