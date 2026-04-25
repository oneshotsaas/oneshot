# OneShot

**A PHP SaaS boilerplate built for vibe coding.**

[![License: MIT](https://img.shields.io/badge/license-MIT-green?style=flat-square)](LICENSE)

---

## The Problem

Building a SaaS from scratch, 40 out of 50 hours go to things that have nothing to do with your idea.

Auth. User dashboard. Admin panel. Subscription billing. Email and Telegram notifications. Activity logs. API with key management. Content pages.

Every SaaS needs them. None of them are your product.

---

## What OneShot Does

OneShot ships all of that on day one.

You open the project — registration works, subscriptions work, admin panel works. You spend the first hour on what actually makes your product different.

```
git clone https://github.com/oneshotsaas/oneshot my-app
```

Open `/install` in the browser. Enter DB credentials, app name, admin account. Done — migrations run, admin created, app is live.

---

## Built for AI and Vibe Coding

Most AI projects stall after 2–3 weeks. Not because the idea is bad — because AI starts every session from zero. No context. No memory of what's already built. Tasks get bigger, AI starts guessing, fixes break other fixes.

OneShot solves this at the architecture level.

Every module has a `modulename.md` — AI reads it before every task and knows exactly what the module does, what methods exist, and how to extend it without breaking anything. The project rules live in `.ai/` and `CLAUDE.md`. Same context, every session.

You don't need to know PHP. You describe what you want. AI writes code that fits the project on the first try.

```
You describe a feature  →  AI reads the architecture  →  AI writes working code
        ↑                                                           ↓
  You ship it           ←     You review one file      ←  It fits the project
```

---

## Learn by Building

**[Ship Your SaaS with AI — Free Video Course](https://www.youtube.com/watch?v=vcjyQflktuI&list=PLgzd8xRXRV96oS4cNYkGV9pNB884c7gEM)**

8 steps. Each one builds on the previous. Real process on a live project — OneShot itself.

| # | Topic |
|---|-------|
| 1 | How the internet works. Picking a language. |
| 2 | Dev environment. VS Code. AI context and prompts. |
| 3 | App architecture — MVC, routes, controllers, models. |
| 4 | API integrations — payments, AI models, external services. |
| 5 | Users and authentication — sign-up, login, access levels. |
| 6 | Deployment — domain, hosting, pushing updates. |
| 7 | Monetization — subscriptions, one-time payments, access control. |
| 8 | Security — what to check before going live. |

By the end: a deployed SaaS with auth, billing, notifications, file storage, API — plus your own feature on top.

---

## What's Included

**Users & Access**
Registration, login, password reset, email confirmation, Google OAuth. User dashboard. Admin user management.

**Billing**
Subscription plans. Credit packs. Promo codes. Payment history. Invoices. Access control by plan. Stripe connected via settings — no code changes.

**Notifications**
Email (Mailgun / Resend / SMTP). Telegram. In-app. All through one unified system.

**API**
Public and authenticated endpoints. API key management in user dashboard. Usage tracking. Rate limiting per key.

**Content**
Pages, posts, categories, tags. Visual editor (Editor.js). Blog, docs, legal pages — from the same admin panel.

**Admin Panel**
User management. Subscription control. Action logs. App settings. Everything to run a SaaS, not just build one.

---

## Four Zones, Ready on Day One

```
Public site     /              ← landing, marketing, blog
User dashboard  /app/          ← what users see after login
Admin panel     /admin/        ← everything to run the product
API             /api/v1/       ← for integrations
```

One config file. No magic.

---

## Module System

Each feature is a separate module. Enable it, disable it, or customize it — without touching anything else.

Want to change the login page? Create your own file. The original stays untouched.

```bash
# Override a view
app/Views/auth/front/login.php   # replaces oneshot/Auth/Views/front/login.php

# Scaffold a new module
php spark make:module Blog       # routes, controller, views, migration, docs
```

---

## Providers

Swap one provider for another — the rest of the project doesn't change.

| What           | Provider                          |
|----------------|-----------------------------------|
| Payments       | Stripe                            |
| Email          | SMTP / Resend                     |
| Notifications  | Telegram / Email / In-app         |

---

## Architecture

```
app/        ← your overrides (highest priority)
modules/    ← your custom modules
oneshot/    ← framework core
```

---

## Who It's For

**Solo founders** who want to go from idea to working product without a dev team.

**Product managers and marketers** who want to test and ship ideas on their own timeline.

**Developers** who are tired of rebuilding the same foundation for every new project.

---

## License

MIT — see [LICENSE](LICENSE)
