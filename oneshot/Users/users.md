# Users

User management for admin panel, user profile editing in the app context, and API access.

## Contexts
- Admin: /admin/users         — list all users
- Admin: /admin/users/{hash}  — view single user (signed ID in URL)
- App:   /app/profile         — view and update own profile
- API:   /api/users           — list, view, create, update, delete users (integer IDs)

## Controllers
- `Admin\Users`   — admin user list and detail view
- `App\Profile`   — current user profile (app context)
- `Api\Users`     — REST CRUD; generic actions via base class, `store` and `delete` overridden

## Profile Page Fields
- **Name** — editable text
- **Email** — readonly
- **Telegram ID** — user's Telegram numeric ID; used by the Telegram notification channel
- **Appearance** — light/dark theme toggle (saved via `setOption`)
- **Notification Preferences** — per-type/per-channel toggle matrix (AJAX, uses `Notifier::updatePreference`)

## API Endpoints
| Method | URL                     | Action         |
|--------|-------------------------|----------------|
| GET    | /api/users              | index (list)   |
| POST   | /api/users              | store (create) |
| GET    | /api/users/{id}         | show           |
| POST   | /api/users/{id}         | update         |
| POST   | /api/users/{id}/delete  | delete         |

Pagination: `?offset=0&limit=20`. Response includes `total`, `limit`, `offset`.

Exposed fields: `id`, `name`, `email`, `role`, `status`, `lang`, `timezone`, `telegram_id`, `email_verified_at`, `created_at`, `updated_at`.

Editable fields (update): `name`, `telegram_id`, `status`, `lang`, `timezone`.

Store required fields: `email`, `password`. Optional: `name`, `lang`, `timezone`, `telegram_id`.
Password is validated against the configured policy and hashed via `AuthService::register()`.

## API Security Decisions
- **`role` is not settable via API** — neither on create nor on update. Role changes must go through admin UI.
- **Admin users cannot be deleted via API** — `delete` returns 403 if `role = admin`. To delete an admin, change their role first.
- **No verification email on create** — `store` passes `send_verification: false, status: active` to `AuthService::register()`. User is created active immediately.

## Models
- Reuses `OneShot\Auth\Models\User` (fields include `telegram_id`)

## Dependencies
- `OneShot\Auth\Models\User`
- `OneShot\Auth\Services\Auth` — `register()` used in `store` for validation, hashing, and creation
- `OneShot\Notifications\Services\Notifier` — `getPreferences($userId)` for profile view
- `config('NotificationTypes')` — type/group registry for preferences matrix
