# Auth

Handles user authentication: login, registration, logout, email verification, password reset, and OAuth social login.

## Contexts

| Route | Description |
|-------|-------------|
| `GET/POST /auth/login` | Login form |
| `GET/POST /auth/register` | Registration form |
| `GET /auth/logout` | Session destroy |
| `GET/POST /auth/forgot` | Forgot password form |
| `GET/POST /auth/reset/{token}` | Password reset form |
| `GET /auth/verify/{token}` | Email verification |
| `POST /auth/verify/resend` | Resend verification email |
| `GET /auth/oauth/{provider}` | Redirect to OAuth provider |
| `GET /auth/oauth/{provider}/{secret}/callback` | OAuth callback |
| `POST /auth/oauth/{provider}/{secret}/callback` | Telegram Login Widget callback |

## Controllers

- `Auth`     — base controller
- `Login`    — login form / processing / logout
- `Register` — registration form / new user creation
- `Forgot`   — forgot password form / sends reset email
- `Reset`    — password reset form / applies new password
- `Verify`   — email verification via token / resend
- `OAuth`    — OAuth redirects, callbacks, Telegram widget

## Models

- `User`          — table `auth_users`; fields: id, email, deleted_email_hash, password, name, role, lang, timezone, status, email_verified_at, telegram_id
- `Token`         — table `auth_tokens`; generic token store for verify/reset/magic-link/invite/2FA
- `OAuthProvider` — table `auth_providers`; links OAuth provider accounts to auth_users

## Services

- `Auth::login($email, $password)` — rate-limited, checks email_verification setting, regenerates session
- `Auth::register($data, $options = [])` — creates user, sends verification email if required/optional; supports options:
  - `send_verification` *(bool)* — override email sending (default: per `auth.email_verification` setting)
  - `status` *(string)* — override initial status (default: `pending` if verification required, otherwise `active`)
  - Example for programmatic creation (no email, always active): `register($data, ['send_verification' => false, 'status' => 'active'])`
  - Before creating, checks `deleted_email_hash` against previously deleted accounts and applies `auth.deleted_email_policy`
- `Auth::resetPassword($userId, $newPassword)` — invalidates all reset tokens, destroys all sessions
- `Auth::loginWithOAuth($provider, $providerUser)` — 3-step: find by provider_id → link by verified email → create
- `Auth::logout()` — destroys session
- `Auth::user()` — returns current user from session
- `MailService::sendVerification($user, $token)` — sends verify email via SMTP
- `MailService::sendPasswordReset($user, $token)` — sends reset email via SMTP
- `OAuthService::getAuthUrl($provider)` — builds OAuth authorization URL, stores state in session
- `OAuthService::handleCallback($provider, $code, $state)` — validates state, exchanges code, returns normalized user object
- `OAuthService::enabledProviders()` — returns providers with enabled=1 AND credentials set
- `OAuthService::callbackUrl($provider)` — returns the full callback URL including secret segment
- `OAuthService::providerSecret($provider)` — HMAC-SHA256 of `'oauth:{provider}'` + app secretKey, first 16 hex chars

## OAuth Callback URL Security

Each provider has a unique secret segment derived from the app's `secretKey`:

```
/auth/oauth/{provider}/{secret}/callback
```

- Secret = `substr(hash_hmac('sha256', 'oauth:{provider}', secretKey), 0, 16)`
- Per-installation (different secret keys → different URLs)
- Per-provider (each provider gets its own secret)
- No DB storage — computed on every request
- Controller validates with `hash_equals()` before any other logic

The callback URL for each provider is seeded as a `readonly` settings field (under Settings → Auth) so admins can copy it into the OAuth provider console. Re-run `php spark auth:setup` after changing domain or `secretKey`.

## Token System

`auth_tokens` is a generic token table reusable for any one-time link:

| type | TTL | Usage |
|------|-----|-------|
| `verify_email` | 24h | Email confirmation link |
| `reset_password` | 60min | Password reset link |
| *(extensible)* | custom | Magic links, invites, 2FA recovery, email change |

- Raw token returned to user (in link), SHA-256 hash stored in DB
- `Token::consume()` — timing-safe, atomic DB transaction (race condition proof)
- `Token::invalidateAll($userId, $type)` — bulk invalidate (called after password reset)
- Soft limit: max 3 active tokens per user+type (oldest deleted on overflow)
- Lazy cleanup on `create()`; global cleanup via `php spark auth:cleanup`

## Account Linking (OAuth)

When a user logs in via OAuth, the service resolves in order:

1. Find `auth_providers` row by `(provider, provider_id)` → log in
2. If provider confirmed `email_verified=true`: find `auth_users` by email → link accounts, log in
3. Create new `auth_users` + `auth_providers` row → log in

Providers that always return verified email: Google, GitHub, Microsoft.
Providers that may return unverified: Facebook, LinkedIn (check flag).
Apple: email only on first login — treated as verified.
Telegram: no email — skips step 2, creates user without email.

## Settings

Seeded automatically via `php spark auth:setup` (idempotent, safe to re-run).

Groups registered: **auth** and **mail**. Appear in `/admin/settings` automatically.

Key settings consumed at runtime:
- `auth.normalize_email` — `0` / `1` — strip `+tags` from all addresses; also strip dots for Gmail/Googlemail domains. Applied in both `login()` and `register()`
- `auth.block_disposable_emails` — `0` / `1` — reject registrations from domains in `EmailDomains::$disposable`
- `auth.blocked_email_domains` — free-text, any non-domain separators — additional domains to block on registration
- `auth.deleted_email_policy` — `allow` / `flag` / `block` — what to do when a deleted account's email is re-used on registration (default: `allow`)
- `auth.email_verification` — `required` / `optional` / `disabled`
- `auth.password_min_length` — minimum password length
- `auth.oauth_{provider}_enabled` — show/hide provider button
- `auth.oauth_{provider}_{credential}` — client ID, secret, etc.
- `mail.smtp_*` — SMTP connection settings
- `mail.from_email`, `mail.from_name` — sender identity

Fallback: if a setting is empty, reads from `.env` (e.g. `OAUTH_GOOGLE_ID`, `MAIL_SMTP_HOST`).

## Migrations

All in `oneshot/Core/Database/Migrations/`:

- `2026-01-01-000000_CreateAuthUsersTable` — `auth_users`
- `2026-03-24-100000_AddEmailVerifiedAtToAuthUsers` — adds `email_verified_at`
- `2026-03-24-100001_CreateAuthTokensTable` — `auth_tokens`
- `2026-03-24-100002_CreateAuthProvidersTable` — `auth_providers`
- `2026-04-04-100004_AddTelegramSupport` — adds `telegram_id VARCHAR(50) NULL` to `auth_users`; seeds `telegram.bot_token`, `notifications.defaults`, `notifications.queue_mode` settings
- `2026-04-17-100000_AddDeletedEmailHashToAuthUsers` — adds `deleted_email_hash VARCHAR(64) NULL` to `auth_users` for fraud detection on re-registration

## Email Domain Config (`oneshot/Auth/Config/EmailDomains.php`)

Three static arrays, all `domain => 1` for O(1) `isset()` lookup:

| Array | Purpose |
|-------|---------|
| `$trusted` | Well-known legitimate providers — disposable check skipped entirely |
| `$dotNormalized` | Domains where dots in local part are insignificant (Gmail, Googlemail) |
| `$disposable` | Bundled list of disposable / temporary email providers |

Update `$disposable` from the community list:
```bash
php spark auth:update-disposable
```

## User Deletion (API)

When a user is deleted via `POST /api/users/{id}/delete`, the record is **anonymised then soft-deleted**:
- `email` → `deleted_{id}@deleted` — frees the address for re-registration
- `deleted_email_hash` → SHA-256 of the original normalised email (`mb_strtolower(trim(...))`) — retained for fraud detection
- `name`, `password`, `telegram_id`, `email_verified_at` → cleared
- Row is soft-deleted (`deleted_at` set); related records (billing, logs) keep their FK intact

On next registration with the same email, `Auth::register()` looks up `deleted_email_hash` and applies `auth.deleted_email_policy`.

Run: `php spark migrate -n "OneShot\\Core"`

## Events

- `user.registered` — after successful registration
- `user.login` — after successful login (including OAuth)

## Commands

- `php spark auth:cleanup` — delete expired and used tokens from `auth_tokens`
- `php spark auth:setup` — seed/update Auth and Mail settings (safe to re-run on existing installs)
- `php spark auth:update-disposable` — fetch latest disposable domain list from GitHub and rewrite `EmailDomains::$disposable`
