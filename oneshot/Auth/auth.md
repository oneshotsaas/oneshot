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

- `User`          — table `auth_users`; fields: id, email, password, name, role, lang, status, email_verified_at
- `Token`         — table `auth_tokens`; generic token store for verify/reset/magic-link/invite/2FA
- `OAuthProvider` — table `auth_providers`; links OAuth provider accounts to auth_users

## Services

- `Auth::login($email, $password)` — rate-limited, checks email_verification setting, regenerates session
- `Auth::register($data)` — creates user, sends verification email if required/optional
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

Run: `php spark migrate -n "OneShot\\Core"`

## Events

- `user.registered` — after successful registration
- `user.login` — after successful login (including OAuth)

## Commands

- `php spark auth:cleanup` — delete expired and used tokens from `auth_tokens`
- `php spark auth:setup` — seed/update Auth and Mail settings (safe to re-run on existing installs)
