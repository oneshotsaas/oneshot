# OneShot

**The PHP framework built for the age of vibe-coding.**

[![Built with OneShot](https://img.shields.io/badge/built%20with-OneShot-6c47ff?style=flat-square)](https://github.com/oneshotsaas/oneshot)
[![License: MIT](https://img.shields.io/badge/license-MIT-green?style=flat-square)](LICENSE)

---

## The Problem

You ask an AI: *"What's the fastest stack to ship a solo SaaS?"*

It says PHP. It suggests Laravel.

You install Laravel. **100MB of code.** 500 pages of docs. Every package — another 500 pages. You spend three days reading documentation before writing a single feature.

And even after that — the AI still hallucinates. It invents methods that don't exist. It writes code that looks right but breaks at runtime. You end up manually reviewing every output anyway.

**The real problems:**

- Every solo developer reinvents the same modules: auth, users, billing, notifications — from scratch, every project
- Documentation-first frameworks were designed for humans. LLMs don't need docs — they need readable, consistent, minimal code
- LLMs can't build an entire project reliably. But they *can* work perfectly inside a well-structured, pre-verified codebase
- Without a framework, there's no shared context — every project is a different dialect, every AI session starts from zero

---

## The Solution

**OneShot** is a minimal PHP boilerplate where the AI already knows everything.

No documentation to read. No conventions to memorize. Just open a chat, describe what you need, and the AI writes working code — because the entire framework fits in a single conversation context.

- **Pre-built, pre-verified modules** — Auth, Users, Billing, Notifications. Already work. Just extend them.
- **LLM-first structure** — every module has a `modulename.md` that tells the AI exactly what the module does, what methods exist, what events it fires
- **CLAUDE.md + `.ai/` folder** — the AI understands the full architecture from the first message
- **Zero framework lock-in** — override any module, any view, any route without touching library code
- **Micro-SaaS ready** — one command scaffolds a new module with routes, controllers, migrations, and docs

---

## How It Works

```
You describe a feature  →  AI reads the codebase context  →  AI writes working code
         ↑                                                              ↓
   You ship it          ←      You review one file          ←  It fits the architecture
```

The AI doesn't guess your conventions. It reads `CLAUDE.md`, `.ai/rules/`, and each `modulename.md` — and produces code that matches your project on the first try.

---

## Quick Start

```bash
git clone https://github.com/oneshotsaas/oneshot my-app
cd my-app
composer install
cp env .env   # set database credentials
php spark migrate --all
php spark serve
```

**Scaffold a new module:**

```bash
php spark make:module Blog
```

That's it. Routes, controller, views, migration, and module docs — all generated.

---

## What's Included

| Module     | What it does                                      |
|------------|---------------------------------------------------|
| **Auth**   | Login, registration, logout, session              |
| **Users**  | Admin user list, profile editing                  |
| **Core**   | Base controllers (Front/Admin/App/API), base model, helpers |

**Providers** (plug in, no core changes):

| Provider   | Interface |
|------------|-----------|
| Stripe     | Payment   |
| Telegram   | Notify    |
| Mailgun    | Mail      |
| S3         | Storage   |

---

## Architecture in 30 Seconds

```
app/        ← your overrides (highest priority)
modules/    ← your custom modules
oneshot/    ← library modules (pre-verified)
system/     ← framework core
```

Four URL contexts, one config file:

```php
// app/Config/Prefixes.php
public string $front = '';      // yoursite.com/
public string $auth  = 'auth';  // yoursite.com/auth/login
public string $app   = 'app';   // yoursite.com/app/dashboard
public string $admin = 'admin'; // yoursite.com/admin/users
public string $api   = 'api/v1';
```

Override anything without touching library code:

```bash
# Override a view
app/Views/auth/front/login.php   # replaces oneshot/Auth/Views/front/login.php

# Override a module
modules/Auth/                    # replaces oneshot/Auth/ entirely
```

---

## The Crazy Idea

> What if you never read framework docs again?

Every file in OneShot is written to be understood by an AI in a single prompt. The architecture is flat, consistent, and documented in plain English inside the codebase itself.

Open your project in Claude, Cursor, or any AI IDE. Ask anything. Get working code. Ship.

**This is what framework development looks like when the developer is the AI.**

---

## Built With OneShot

Are you building with OneShot? Add the badge to your README:

```markdown
[![Built with OneShot](https://img.shields.io/badge/built%20with-OneShot-6c47ff?style=flat-square)](https://github.com/oneshotsaas/oneshot)
```

---

## License

MIT — see [LICENSE](LICENSE)
