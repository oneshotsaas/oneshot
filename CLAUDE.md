# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

---

## Workflow — Follow This Every Time

```
1. Read .ai/index.md                         ← entry point, always
2. Read .ai/rules/general.md                 ← mandatory rules, always
3. Read .ai/rules/ui-ux.md                   ← mandatory for any view work (layout, topbar, tables, mobile)
3b. Read .ai/rules/modules.md               ← when creating or editing modules
4. Read ONESHOT.md                           ← architecture overview (if needed)
5. Read {module}/modulename.md               ← for the module you're working in
6. Read the relevant .ai/rules/ or skill     ← based on task type
7. Write code
```

Never start writing code before completing steps 1–5.

---

## Project

**OneShot** — a minimal PHP boilerplate designed for AI-assisted development.
Full architecture reference: `ONESHOT.md`

Key idea: the entire framework is readable in one prompt. No docs needed.
The AI asks questions in chat, gets answers, ships working code.

---

## Commands

```bash
php spark list                                    # all available commands
php spark routes                                  # verify registered routes
php spark migrate -n "OneShot\\Core"              # run all migrations (all live in oneshot/Core/Database/Migrations/)
php spark db:seed DatabaseSeeder                  # seed initial data
php spark make:module ModuleName                  # scaffold a new module in modules/
php spark make:migration ModuleName X             # create a migration inside a module
php spark tasks:run                               # process queued notification tasks (email, Telegram)
```

---

## Architecture

**Layer priority (highest → lowest):**
```
app/        ← global overrides
modules/    ← user custom modules
oneshot/    ← library modules (pre-verified)
system/     ← framework core (do not touch)
```

**Namespace map** (`app/Config/Autoload.php`):
```
App\       → app/
Modules\   → modules/
OneShot\   → oneshot/
Providers\ → providers/
```
Sub-namespaces are registered at boot by `oneshot/Core/Loader.php` via the `pre_system` event.

**Controller hierarchy:**
```
Base → Front  (layout: Core::layouts/front)
     → Admin  (layout: Core::layouts/admin)
     → App    (layout: Core::layouts/app)
     → Api    (ok() / fail(), no layout)
```

**URL prefixes** — single config: `app/Config/Prefixes.php`, read via `config('Prefixes')`.

**Filters** — registered as aliases in `oneshot/Core/Config/Filters.php`, applied on route groups only.

**View overrides** — `app/Views/auth/front/login.php` overrides `Auth::front/login`.

**Module overrides** — `modules/Auth/` overrides `oneshot/Auth/` (Loader registers modules/ first).

---

## Git Workflow

Branches: `main` (stable) and `dev` (working).

**Rules:**
- All work goes to `dev` — commit and push to `dev` only
- Never push directly to `main`
- `main` ← `dev` only when the release is confirmed (via PR on GitHub)
- Never add `Co-Authored-By` lines to commit messages

```bash
git add <files>
git commit -m "description"
git push origin dev
```

---

## Key Files

| File | Purpose |
|------|---------|
| `.ai/index.md` | AI entry point — what to read and when |
| `.ai/rules/general.md` | Mandatory coding rules |
| `ONESHOT.md` | Full architecture reference |
| `app/Config/Prefixes.php` | URL prefix config |
| `oneshot/Core/Loader.php` | Dynamic sub-namespace registration |
| `oneshot/Core/Controllers/Base.php` | `render()`, `share()`, `appendBC()`, `redirectWith()` |
| `oneshot/Core/Models/Base.php` | `getAll()`, `getOne()`, `getById()`, `add()`, `addGet()`, `getOrAdd()` |
| `oneshot/Core/Helpers/oneshot.php` | `render()`, `signId()`, `signedId()`, `l()`, `rds()`, `__()`, `encrypt()`, `decrypt()`, `event()`, `notify()`, `activity()`, `clientIp()` |
