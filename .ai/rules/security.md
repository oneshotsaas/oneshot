# Security Rules

Mandatory for every task that touches user input, database access, auth, payments, file uploads, or API endpoints.
Read this file on every such task — not just when explicitly asked to "do a security review."
For module-specific detail (Auth, API, Keys, Billing, Content/uploads, SSRF, race conditions, headers, general web/microservice topics, OWASP mapping) → **`.ai/rules/references/security.md`**.

---

## Priority

**Critical** — get these right or don't ship: Authentication · Authorization/ownership · Billing/payments · API keys · File uploads · External webhooks.

**High**: API endpoints · User input handling · Settings · Notifications.

**Medium**: UI rendering (output escaping still matters, just lower blast radius) · Logs · Cache.

If unsure which bucket a change falls into, treat it as the higher one.

---

## Before Finishing Any Task — Verify

```
✓ authentication      — is the route/action behind the right filter?
✓ authorization       — does it check ownership or admin role, not just "logged in"?
✓ input validation    — validated at the boundary, types checked after JSON decode?
✓ output escaping     — esc() on every user-controlled value in a view?
✓ CSRF                — csrf_field() on every POST form?
✓ ownership checks    — resource belongs to service('auth')->user(), or admin-scoped?
✓ secrets             — nothing hardcoded, nothing logged, nothing echoed raw?
✓ rate limiting       — service('throttler') on login/register/reset/API mutations?
✓ logging             — l() calls contain no passwords/tokens/payment payloads?
✓ SQL parameterization — no string-interpolated where()/query()?
```

---

## Authorization ≠ Authentication

Being logged in only proves *who* the user is, not *what* they're allowed to touch.

Every operation that loads or modifies a resource must verify:
- the resource belongs to the current user, **or**
- the current user has admin permission for it.

Never assume "the user is authenticated" is enough to serve or mutate a record by ID. This is the single most common mistake an AI makes when writing CRUD code (IDOR) — see `references/security.md` §3.

---

## Never

- Never disable CSRF, a filter, or a rate limit "to make it work" — fix the actual blocker instead.
- Never skip an ownership/admin check on a resource lookup.
- Never trust client-side validation as the only validation.
- Never trust `request->getPost('user_id')` / any client-supplied user/owner ID — resolve the acting user from the session/token.
- Never put `role` (or any privileged field) in an API `$editable`/`$creatable` list.
- Never use `@` to suppress errors.
- Never expose stack traces, SQL errors, or file paths to the end user — generic message to the user, full detail to the log.
- Never use `eval()`.
- Never `unserialize()` external/user-controlled input — use JSON.
- Never log or echo secrets (passwords, tokens, API keys, webhook signing secrets) — not in logs, not in exceptions, not in AI prompts/telemetry.
- Never fetch a user-supplied URL server-side without scheme + private-IP validation, a timeout, and a max response size (SSRF).
- Never trust a webhook payload without verifying its signature first.
- Never cache authenticated/personalized responses or CSRF tokens.
- Never let user-supplied text control an LLM system prompt or leak secrets into a prompt sent to a third-party model.

---

## If You're Not Sure

- Ask instead of guessing.
- Implement the safer option by default.
- Do not silently weaken an existing protection to unblock yourself.

## When Modifying Existing Code

Preserve what's already there unless explicitly told to change it: filters, CSRF, rate limits, ownership checks, transactions/row-locking, logging. If a security mechanism is in your way, that's a signal to ask, not to remove it.
