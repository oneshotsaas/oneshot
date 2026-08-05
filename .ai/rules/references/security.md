# Security Reference (Full Detail)

Detailed, module-by-module security reference for OneShot. **`.ai/rules/security.md` is the mandatory short checklist read on every task** — come here for the specifics of the module/area you're touching (Auth, API, Keys, Billing, Content/uploads, SSRF, race conditions, headers, general web/microservice topics, OWASP mapping) or when doing a full security review.

---

## 1. Core Principles (apply everywhere)

- **Validate at boundaries only** — user input, external API responses, webhook payloads, file uploads. Trust internal code/framework guarantees; don't re-validate data you already control.
- **Escape all output** — `esc()` on every user-controlled value printed in a view. Never `echo` raw request data, DB values that originated from user input, or query strings.
- **CSRF on every form** — `<?= csrf_field() ?>` in every HTML `<form method="post">`. No exceptions, including admin-only forms.
- **Never expose internal IDs** — use `signId()` / `signedId()` for any ID that appears in an HTML URL. Raw integer IDs are only acceptable in `/api/*` JSON responses (documented exception, see `.ai/rules/api.md`).
- **DB access only through models** (`extends OneShot\Core\Models\Base`) — never raw PDO/query builder calls in controllers. Models use CodeIgniter's query builder, which parameterizes bound values — never concatenate user input into a `where()`/`like()` raw string.
- **Rate-limit sensitive endpoints** — `service('throttler')` on login, register, password reset, OTP/2FA, API mutations, webhook-triggered actions, key validation.
- **Secrets never hardcoded** — always `.env` / config / encrypted `settings` rows. Never commit API keys, webhook secrets, or credentials.
- **Least-privilege routes** — apply `filter` on route *groups*, never globally, and never accidentally leave a group without a filter when it needs one (`admin`, `auth`, `api`, `api-key`).
- **Logging must not leak secrets** — `l($data, 'context')` must never receive raw passwords, tokens, API secrets, or full credit card/payment payloads.

### How to check in code review
- Grep new view files for raw `<?= $` without `esc()` around anything sourced from `$_GET`/`$_POST`/DB text fields.
- Grep new `<form method="post"` blocks for missing `csrf_field()`.
- Grep controllers for `route_to(...signId(` vs plain `$item->id` leaking into `route_to()`/URLs.
- Grep for string-concatenated `where("... $var")` / `->query("...")` with interpolated variables.
- Check every new route group declares a `filter`.

---

## 2. Authentication & Sessions (`Auth` module)

- Passwords hashed via `AuthService::register()` / CI4's password hashing — never write custom hashing.
- `Auth::login()` is rate-limited and regenerates the session ID on success — don't bypass by calling lower-level session code directly.
- `Auth::resetPassword()` invalidates **all** reset tokens and destroys **all** sessions for that user — preserve this behavior if touching password-reset flow (prevents stale token reuse and session fixation after a compromise).
- Password reset / email verification tokens live in `auth_tokens` (generic token store) — always check expiry and single-use invalidation when adding a new token type; never reuse a token record for a second purpose.
- **OAuth callback URLs** carry a per-provider secret segment (`HMAC-SHA256('oauth:{provider}' + secretKey)`) — this is the CSRF/guessing protection for the callback route since it's unauthenticated. Never remove or make this segment optional, and never log the full callback URL (it contains the secret).
- OAuth linking is by **verified email only** (`findByProviderId → link by verified email → create`) — never auto-link by unverified email, that allows account takeover via a spoofed OAuth identity.
- `deleted_email_hash` check + `auth.deleted_email_policy` on registration — don't let a new account silently reuse a previously-deleted account's data/history without going through this policy check.
- Session-dependent code must guard for CLI context (`if (is_cli()) return;`) — sessions/requests don't exist there.

### What to check
- New auth-adjacent routes: are they in the `public` allowlist appropriately, or accidentally open?
- Any new token-based flow: expiry + single-use + tied to correct user?
- Any change to `Auth::login`/`resetPassword`: still rate-limited, still regenerates/destroys sessions?

---

## 3. API Endpoints (`Controllers/Api/`, see `.ai/rules/api.md`)

- Extend `OneShot\Core\Controllers\Api`; only list truly public actions in `protected array $public`.
- `$fields` (exposed on read) must **never** include `password` or any secret/hash column.
- `$editable` / `$creatable` must **never** include `role` — privilege escalation via mass-assignment is the #1 API risk in this framework. Role changes go through the admin UI only.
- Override `delete()` to block destructive actions on privileged records (e.g. admin users) when applicable — see `Users` module's 403-on-admin-delete pattern.
- API responses use real integer IDs by design — do not sign/hash them there, but never let integer IDs leak into places that expect ownership checks to already have happened; always verify the resource belongs to `service('auth')->user()` (or is admin-scoped) before returning/mutating it, IDOR is the main risk of un-signed IDs.
- Only GET/POST — if you see a route needing DELETE semantics, it must be `POST {id}/delete`, and that action must still enforce ownership/role checks (not just "authenticated").
- Input is `getJSON(true) ?: getPost()` — validate types after decoding; a JSON body can send arrays/objects where a scalar is expected.

### What to check
- New `Api` controller: `$fields` excludes secrets? `$editable`/`$creatable` excludes `role`? `store()` written manually (no generic implementation) and validates input?
- Any per-resource action (`show`/`update`/`delete`): does it verify the record's owner/user_id matches the authenticated user, or is it admin-only?

---

## 4. API Keys (`Keys` module)

- Keys are `{prefix}{key_id}:{secret}` — only the SHA-256 **hash** of the secret is stored; the raw secret is shown once at creation and never persisted or logged.
- `key_id` is public/enumerable by design (routing/lookup key); the secret is the actual credential — never put the raw secret in a URL, log line, or activity/event payload.
- `validateAndTrack()` does `FOR UPDATE` row locking + hash verification + limit check atomically — if you add new key-consuming logic, don't split this into separate non-atomic calls (race condition would let usage limits be bypassed).
- `Filters/ApiKey.php` reads `X-API-Key` or `Authorization: Bearer` — new API routes needing key auth should use the `api-key` filter alias, with credit cost declared per-route (`'filter' => 'api-key:5'`), not reimplement key parsing.

### What to check
- Any new code touching `keys_keys`: does it ever `SELECT`/log/return the `secret` column? (It shouldn't even exist as stored data — verify you're comparing against the hash.)
- Any new usage-limited action: uses `validateAndTrack()`'s atomic pattern, not a read-then-write race.

---

## 5. Billing / Payments (`Billing` module)

- Ledger is **immutable** and operations are **atomic** — `charge()`/`chargeForce()` must not be reimplemented ad hoc; always go through `BillingService`.
- `chargeForce()` **requires an idempotency key** — every call site must pass a stable, unique key (e.g. derived from the provider's webhook/event ID) so retried webhooks or duplicate requests never double-charge.
- Webhook handlers (Stripe/Coinbase) are unauthenticated-by-necessity public endpoints — they **must** verify the provider's signature (Stripe webhook signing secret, etc.) before trusting any payload. Never trust `amount`/`user_id`/`status` fields from a webhook body without signature verification.
- `billing_provider_refs` maps external provider IDs to internal records — always look up by this mapping, never trust a provider ID passed directly from client-side/query params as authoritative for which local user/subscription it affects.
- Provider secret keys (Stripe secret key, webhook signing secret) are stored as encrypted `settings` rows (`type=text`, AES-256-CTR at rest) — never log them, never echo them back in any admin view "raw" (mask beyond the first few characters if you must display them).
- `estimate()`/`can_afford` checks must happen **before** triggering a paid external action; use `charge()` pre-flight where the action is cancellable, `chargeForce()` post-flight only when the cost isn't known until after execution.

### What to check
- Any new billable action: pre-flight `charge()`/`estimate()` or documented reason for post-flight `chargeForce()` with idempotency key?
- Any new webhook route: signature/HMAC verification present before any DB write?
- Any code reading Stripe/Coinbase settings: never logged or rendered unmasked?

---

## 6. Content Module (uploads, Editor.js, URL resolution)

- **File uploads** (`admin/content/upload-image`) — validate MIME type and extension server-side (never trust client `Content-Type`), enforce a size limit, and store under `uploadPath`/`uploadUrlPath` from `Config/Content.php` — never let the upload write outside that directory (path traversal via filename). Generate a safe, non-user-controlled filename (e.g. hash/uuid + validated extension), don't reuse the client-provided filename as-is.
- **`fetch-url`** endpoint fetches an arbitrary URL server-side (link preview/meta) — this is SSRF-shaped. Restrict to `http(s)` schemes, reject internal/private IP ranges (`127.0.0.1`, `169.254.169.254` cloud metadata, `10.*`, `192.168.*`, `172.16-31.*`) before making the request, and set a request timeout.
- **`editorjs_render()`** converts Editor.js JSON to HTML and explicitly "sanitizes raw HTML" per its own doc — any change to this function must preserve sanitization of the `raw` block type; Editor.js content is admin-authored today, but if this ever becomes user-submitted, XSS via raw HTML blocks is the primary risk.
- URL resolver walks catch-all `(:any)` routes and does DB lookups per segment — validate/normalize the path before using segments as lookup keys (already slug-based, but any new lookup type added here should stay parameterized, not raw SQL).
- Cache (`Resolver::flushContentCache()`) has no TTL — a missed flush after a permission-relevant change (e.g. unpublishing/soft-deleting an item) can serve stale-but-still-public content; always call the flush in every write path (already required by `.ai/rules/general.md` for correctness — it's also a security concern: stale cache = access to content that should now be gone).

### What to check
- Any new upload endpoint: extension/MIME whitelist, size limit, non-predictable stored filename, upload dir outside webroot execution (or `.htaccess`/`web.config` denies script execution in `uploads/`)?
- Any new server-side fetch of a user-supplied URL: scheme + private-IP blocklist present?
- Any change to `editorjs_render`/raw-HTML handling: sanitization still applied?

---

## 7. Notifications

- Notification `data` payload is stored as JSON and later rendered — treat any user-controlled string reaching `title`/`url`/`data` as untrusted; it must be `esc()`-ed wherever rendered in the inbox/bell view, same as any other output.
- `Notifier::notify()` fans out to channels (email/Telegram) — never let a user-controlled `type`/`title` be used to construct a raw SQL query or shell/API call; these are template inputs, not identifiers.
- Email/Telegram channels enqueue async tasks — validate recipient (user_id → looked-up address/chat-id from DB), never accept a raw destination address/chat-id from client input for a notification meant for "the current user."

---

## 8. Settings

- Setting values are AES-256-CTR encrypted at rest (`Setting::store()`) — this is for secrets like API keys; don't bypass by writing directly to the `settings` table with raw SQL.
- Theme file path is sanitized to `[a-z0-9\-]` only before being used as a filename (`writeCssFile`) — any new code building a filename from a `section`/`mode`-like user-influenced value must apply the same whitelist to prevent path traversal.
- `userOption()`/`setOption()` scope to a `user_id` — always pass the authenticated user's ID explicitly or rely on session resolution; never let a client-supplied `user_id` parameter control whose settings get read/written without an ownership/admin check.

---

## 9. Activity Log

- `activity_logs.metadata` is filtered to scalar values only before storage by design — don't "fix" this to allow nested objects without checking it can't be used to smuggle large/sensitive payloads (e.g. full request bodies) into the audit log.
- Activity log is an admin-visible audit trail — never log raw passwords, tokens, API secrets, or full payment payloads into `metadata`, even during debugging.

---

## 10. Credential Stuffing & Brute Force

- Login must stay rate-limited (`service('throttler')`, see §2) **and** should not reveal whether the email or the password was wrong (generic "invalid credentials" message) — prevents account enumeration that feeds credential-stuffing lists.
- Consider account lockout / progressive delay after repeated failures from the same IP or against the same account, not just a flat throttle window.
- 2FA (if/when added) must be enforced at login, not just available — an optional 2FA that a compromised-password attacker can skip provides no protection.

## 11. CORS

- Do not enable `Access-Control-Allow-Origin: *` (or reflect the request `Origin`) together with `Access-Control-Allow-Credentials: true` — that combination lets any external page read authenticated API responses using the victim's session/cookies.
- API routes (`api-key` filter, `/api/v1/*`) that are meant for server-to-server or key-based access generally need **no** CORS headers at all — don't add permissive CORS "to make the frontend work" without checking whether the call should instead go through the same-origin app routes.
- If browser-based cross-origin access is genuinely required, whitelist exact origins explicitly (never `*` with credentials) and scope it to the specific routes that need it.

## 12. Dependency Security

- New Composer/npm packages must be checked before adding: reputable maintainer, recent activity, no history of supply-chain incidents — don't add a package solely because a blog post/influencer recommended it without checking its source.
- Run `composer audit` (and `npm audit` for any JS deps) periodically and after adding packages — known-vulnerable dependencies are a common intrusion path when left unpatched for months.
- Pin dependency versions in `composer.lock`/`package-lock.json` (already the default) — don't bypass lockfiles with `--no-lock` or manual version floats in ways that let an update silently pull in a compromised release.
- Never let install scripts run with elevated/production credentials in CI — a malicious package's postinstall script is a common way `.env` secrets get exfiltrated.

## 13. Race Conditions

- Not just Billing — any "check balance/limit/stock then act" sequence is a race condition risk if two requests can run concurrently. Use the same pattern as `Keys::validateAndTrack()` (row lock `FOR UPDATE` + check + increment in one atomic operation) for: promo code redemption, one-time-use tokens, inventory/stock decrements, "first N users only" logic, and any credit/usage deduction outside Billing.
- Never implement "read current value in PHP, compare, then write" for anything security- or money-relevant — always push the check into the atomic DB operation (row locking, `UNIQUE` constraint + catch, or `UPDATE ... WHERE balance >= cost`).

## 14. Session & Cookie Security

- Session cookies must be `HttpOnly` (prevents JS/XSS from reading them) and `Secure` (HTTPS-only) — verify `app.Config/App.php` / session config keeps these enabled; don't disable `HttpOnly` to "debug via document.cookie."
- `SameSite=Lax` (or `Strict` where the flow allows) reduces CSRF exposure as defense-in-depth alongside `csrf_field()` — don't weaken it to `None` without a documented cross-site need plus `Secure`.
- Because session theft via XSS bypasses password/2FA entirely, the `esc()`-everywhere rule (§1) is also the primary defense against session hijacking, not just cosmetic — treat any missed `esc()` in a logged-in view as high severity.

---

## 15. Framework & Infrastructure Hardening (CI4 / PHP-specific)

- **`.env` protection** — `CI_ENVIRONMENT` must be `production` on the live server, never `development`. Debug mode in production leaks stack traces (file paths, DB queries, sometimes `.env` values) to any visitor who triggers an error. Verify `app/Config/Boot/production.php` has `display_errors` off.
- **Security headers** — set `X-Frame-Options: DENY` (or `SAMEORIGIN` if embedding is needed) to prevent clickjacking, `X-Content-Type-Options: nosniff`, and a `Content-Security-Policy` restricting script sources, especially since views use `esc()` as the main XSS defense — CSP is defense-in-depth for the cases that slip through. CI4 ships a **`SecureHeaders` filter** for exactly this — prefer enabling/configuring it over hand-rolling headers in a custom `oneshot/Core` response filter.
- **HSTS** — `Strict-Transport-Security` header once the site is HTTPS-only, so a stray `http://` link can't downgrade a session. Pair with `Config\App::$forceGlobalSecureRequests = true` and/or `force_https()` to hard-redirect any `http://` request before it's processed.
- **Model mass-assignment guard** — every model extending `Base` should declare `$allowedFields` explicitly (native CI4 model property, maps to OWASP API3 "Broken Object Property Level Authorization"); this is the model-layer backstop if a controller ever forgets to filter `$editable`/`$creatable` before a `save()`/`update()` call (see §3).
- **Timing-safe comparisons** — token/secret comparisons (reset tokens, API key hash, webhook signatures, OAuth `state`) must use `hash_equals()`, never `==`/`===`, to avoid timing side-channel attacks that let an attacker guess a valid value byte-by-byte.
- **Encryption/secret key management** — `secretKey` (used for `signId()`, OAuth callback secrets, AES-256-CTR `settings` encryption) must never be committed, must differ between environments, and must be backed up separately from the DB — losing it or rotating it silently breaks all signed URLs/decryption of stored secrets; rotating it must be a deliberate, documented operation, not incidental. For DB-level encryption of a whole column (beyond the `settings` table's own AES-256-CTR), CI4's database config also supports an `encrypt` connection option — prefer the existing `Setting`/encryption helper pattern already in this codebase over introducing a second encryption mechanism.
- **Directory/file exposure** — `uploads/`, `writable/`, and any generated theme CSS directories must not be script-executable (no PHP execution in `public/uploads`) and must not allow directory listing.
- **PII & log retention** — activity logs, notification data, and settings can accumulate personal data (email, Telegram ID, IP). Have a retention/rotation policy in mind for `activity_logs` and don't add new PII fields to logs without checking whether they need to be there (data-minimization; relevant if the product ever needs GDPR-style deletion).
- **Telegram / webhook-style integrations** — Telegram bot webhook and Login Widget callbacks must validate Telegram's `hash` field (HMAC of the payload with the bot token) before trusting the identity, the same principle as Stripe webhook signature checks in §5 — never trust an inbound "this user is X" claim without verifying its signature.
- **CI4 built-in checks** — run periodically, especially before a release: `php spark config:check` (flags misconfigured/insecure config values) and `php spark phpini:check` (flags risky `php.ini` settings, e.g. `display_errors`, `expose_php`). Also run `php spark routes` to review the full route table for accidentally-public or unfiltered routes (maps to OWASP "Improper Inventory Management").
- **CORS** — CI4 ships a built-in **CORS filter**; if browser-based cross-origin access is genuinely needed (§11), configure that filter with an explicit origin whitelist rather than writing custom CORS header logic.
- **URI/input character filtering** — CI4's URI Security and `InvalidChars` input filter reject malformed/suspicious characters in the URL and request data before your code ever sees them; don't disable these filters to "allow special characters" without understanding why they were rejected in the first place.
- **CodeIgniter Shield** — CI4's official auth/authorization package (session hardening, 2FA, permissions) is a reference point if OneShot's hand-rolled `Auth` module ever needs a feature (2FA, magic-link, granular permissions) — check whether Shield already solves it in a hardened way before building a custom version.

---

## 16. Beyond This Codebase — General Web App / Microservice Security

Applies even if OneShot later calls out to, or is split into, separate services (background workers, queue consumers, third-party AI/payment APIs, internal microservices).

- **Business logic abuse** — validating types/CSRF/auth isn't enough; check for logic flaws an attacker can drive through legitimate-looking requests: negative quantities/amounts, applying a promo code twice via parallel requests (→ §13), skipping a required step by calling a later-step endpoint directly, price/amount computed client-side and trusted server-side (always recompute cost/price server-side, e.g. Billing's `estimate()`, never trust a client-sent `amount`).
- **Open redirect** — any "redirect back to `?next=`/`?return_url=`" parameter must be validated against a same-origin whitelist; an unchecked redirect is used for phishing (`yourapp.com/redirect?next=evil.com`) even though it looks like it comes from a trusted domain.
- **Host header injection** — don't build absolute URLs (password reset links, OAuth redirect URIs) from the incoming `Host` header without validating it against the configured `app.baseURL`; a spoofed `Host` can make reset-password emails point to an attacker's domain.
- **Insecure deserialization** — never `unserialize()` user-controlled data (cookies, cache keys, imported files); use JSON. PHP object injection via `unserialize()` is a classic RCE vector.
- **Path traversal on downloads/reads** — any endpoint that reads a file by a user-supplied name/path (not just uploads, also exports/downloads/log viewers) must resolve and confirm the real path stays inside the intended directory (`realpath()` check), rejecting `../` sequences.
- **ReDoS** — regexes built from user input (or applied to long user input) should avoid catastrophic-backtracking patterns (nested quantifiers like `(a+)+`); a crafted string can hang a worker/request.
- **Service-to-service auth (microservices)** — if OneShot ever calls or is called by another internal service, don't rely on network placement ("it's internal, so no auth needed") — use mTLS, signed service tokens, or shared-secret HMAC per request, since internal networks get breached too (lateral movement).
- **Secret scoping per integration** — give each third-party API key/service account the minimum scope it needs (e.g. a Stripe restricted key instead of the full secret key where possible); a leaked narrowly-scoped key limits blast radius.
- **Queue/task consumer trust** — `tasks:run` / async job payloads (email, Telegram, billing webhooks) must be treated as data, not code — never build a task payload that gets `eval()`'d, and validate the payload shape when dequeuing (a compromised queue entry shouldn't be able to trigger unintended actions).
- **Resource exhaustion / DoS at the app layer** — cap upload sizes, request body sizes, pagination `limit` (already capped at 100 in the API), and any recursive operation (e.g. `Content` category tree depth is capped at `maxDepth` — keep similar caps on any new recursive/user-driven structure).
- **Admin surface exposure** — consider IP-restricting or adding a non-guessable path segment for `/admin` in addition to the `admin` filter, especially once brute-force/credential-stuffing protections (§10) are in place but before 2FA exists; defense-in-depth for the highest-value target in the app.
- **Third-party AI/LLM calls (if applicable)** — treat any user-supplied text passed to an LLM or external AI API as untrusted input to that system too: don't let user text control system prompts/instructions (prompt injection into internal tooling), and don't pass secrets/internal data into a prompt that gets echoed back to the user or logged by the provider.
- **Incident readiness** — know where secrets can be rotated quickly (Stripe keys, `secretKey`, API key prefix) and keep a mental/documented runbook: rotate compromised secret → invalidate sessions/tokens (`Auth::resetPassword` pattern) → review `activity_logs` for the affected window. Security work isn't done at "no known vuln" — plan for "when, not if."

---

## 17. General Web Vulnerability Checklist (map to OWASP Top 10)

| Risk | Where it shows up here | Mitigation already in framework | What to double-check |
|---|---|---|---|
| SQL Injection | Any model/query builder use | CI4 query builder parameterizes bound values | No raw string concatenation into `where()`/`query()` |
| XSS | Views, notification titles, Editor.js raw HTML | `esc()`, Editor.js sanitizer | Every new output of user data is escaped |
| CSRF | All POST forms | `csrf_field()`, CI4 CSRF filter | Every form has the token; AJAX POSTs send CSRF header/token |
| Broken Access Control (IDOR) | API `show`/`update`/`delete`, Keys, Settings user_id | `signId()` for HTML, ownership checks expected in controllers | Every resource lookup by ID checks ownership or admin role |
| Mass Assignment / Privilege Escalation | API `$editable`/`$creatable` | `role` excluded by convention | Never add `role`, `status` (where sensitive), or other privileged fields to editable/creatable lists without explicit review |
| SSRF | Content `fetch-url`, any outbound webhook/provider call | none automatic | Scheme + private-IP validation on any server-side fetch of a user-influenced URL |
| Insecure File Upload | Content image upload | `uploadPath` config | MIME/extension validation, size limit, non-executable storage path, randomized filename |
| Secrets Exposure | Billing provider keys, API key secrets, `.env` | AES-256-CTR at rest for settings, SHA-256 hash-only for API key secrets | Never log/echo raw secrets; mask in admin UI |
| Session Fixation | Auth login/reset | Session regenerated on login, destroyed on reset | Preserve this behavior in any auth-flow change |
| Webhook Spoofing | Billing payment webhooks | none automatic — must be added per provider | Signature verification before trusting payload |
| Rate Limiting / Brute Force | Login, register, reset, API mutations, key validation | `service('throttler')` | Applied to every new sensitive/public endpoint |

---

## How to Run a Security Pass on New Code

1. Identify which sections above apply (which modules/areas the diff touches).
2. For each touched file, check the module-specific bullet points and the general checklist in §1 and §10.
3. Grep-based spot checks (fast, catches most regressions):
   - `esc(` presence near any `$_GET`/`$_POST`/user-sourced variable echoed in views
   - `csrf_field()` in every new `<form method="post">`
   - `signId(` around IDs used in `route_to()`/HTML `href`
   - No raw `role` in any `$editable`/`$creatable` array
   - No secret/password/token column in any API `$fields` array
   - No string-interpolated SQL (`->query("... $var ...")`, `->where("... $var")`)
4. For anything touching payments, uploads, or external URL fetches — treat as high-risk by default and require signature/type/scheme validation even if not explicitly asked.
