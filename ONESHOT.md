# OneShot Framework

A modular PHP framework for building web applications with multiple contexts (public front, admin panel, user app, REST API). Provides a layered override system, reusable library modules, and contracts for external service integrations.

---

## Directory Structure

```
app/                          ← Global overrides (highest priority)
├── Config/
│   ├── Autoload.php          ← PSR-4 namespace registration (4 entries)
│   ├── Prefixes.php          ← URL prefix configuration (single source of truth)
│   └── Routes.php            ← Optional: override specific routes
└── Views/                    ← Optional: override any module view
    └── auth/front/login.php  ← Overrides OneShot\Auth Views/front/login.php

modules/                      ← User custom modules (second priority)
├── Front/                    ← Custom public site
└── Auth/                     ← Can fully or partially override oneshot/Auth

oneshot/                      ← Framework library modules (third priority)
├── Core/                     ← Framework kernel
│   ├── Loader.php            ← Registers sub-namespaces at bootstrap
│   ├── Controllers/          ← Base, Front, Admin, App, Api
│   ├── Models/Base.php
│   ├── Services/Base.php
│   ├── Filters/              ← Auth, Admin, ApiFilter
│   ├── Contracts/            ← Payment, Notify, Storage, Mail interfaces
│   ├── Helpers/oneshot.php   ← signId, l, rds, __
│   ├── Commands/             ← make:module, make:migration
│   ├── Views/layouts/        ← front, admin, app, _head, _flash, _breadcrumbs
│   └── Config/               ← Events.php (auto-discovered), Filters.php
├── Auth/                     ← Authentication module
├── Users/                    ← User management and profile
├── Settings/                 ← App settings
├── Billing/                  ← Subscriptions and payments
├── Content/                  ← Pages, posts, categories, tags + URL resolver
├── Media/                    ← File uploads (planned)
└── Notify/                   ← Notification dispatch (planned)

providers/                    ← External service implementations
├── Stripe/Stripe.php         ← implements Payment
├── Telegram/Telegram.php     ← implements Notify
├── Mailgun/Mailgun.php       ← implements Mail
└── S3/S3.php                 ← implements Storage

.ai/                          ← LLM tooling
├── agents/                   ← Agent definitions (reviewer, migrator)
├── skills/                   ← How-to guides (make-module, add-provider)
└── rules/                    ← Development rules (general, modules, api)

system/                       ← framework core (unmodified)
```

---

## Layer Priority

```
app/          (highest — global user overrides)
  ↓
modules/      (user custom modules)
  ↓
oneshot/      (framework library modules)
  ↓
system/       (framework core)
```

The `Loader` registers `modules/` namespaces before `oneshot/` namespaces, so auto-discovery picks up user modules first.

---

## Namespaces

| Namespace    | Path          | Purpose                          |
|--------------|---------------|----------------------------------|
| `App\`       | `app/`        | Global overrides and config      |
| `Modules\`   | `modules/`    | User-created custom modules      |
| `OneShot\`   | `oneshot/`    | Framework library modules        |
| `Providers\` | `providers/`  | External service implementations |

Registered in `app/Config/Autoload.php`. Sub-namespaces (e.g., `OneShot\Auth\`) are registered dynamically at `pre_system` by `OneShot\Core\Loader`.

---

## Controller Hierarchy

```
Controller
  └── OneShot\Core\Controllers\Base      render(), share(), appendBC(), redirectWith()
        ├── Front   layout: Core::layouts/front   + setMeta()
        ├── Admin   layout: Core::layouts/admin
        ├── App     layout: Core::layouts/app
        └── Api     ok(), fail(), checkToken()
```

All context controllers are `abstract`. Module controllers extend the appropriate context.

---

## Module Structure

```
ModuleName/
├── modulename.md              ← Module documentation (see format below)
├── Config/Routes.php          ← Routes reading config('Prefixes')
├── Controllers/
│   ├── ModuleName.php         ← Base (single context: flat structure)
│   ├── Login.php
│   — OR (multiple contexts) —
│   ├── Front/
│   ├── Admin/
│   └── Api/
├── Models/Entity.php
├── Services/                  ← Optional, only when needed
├── Views/front/, admin/, app/
└── Database/Migrations/
```

**Rule**: one context → flat `Controllers/`. Two or more → subdirectories per context.

### modulename.md Format

```markdown
# ModuleName

Purpose in 1-2 sentences.

## Contexts
- Front: /prefix/...  — what it does
- Admin: /admin/...   — what it does

## Controllers
- `Name` — description

## Models
- `Entity` — table `module_entity`, key fields

## Services
- `Service::method($params)` — description

## Events
- `module.event` — when triggered

## Dependencies
- uses: OtherModule\Models\Entity
- triggers: Events user.registered
```

---

## URL Conventions

Configure once in `app/Config/Prefixes.php`:

| Property | Default    | Example URLs             |
|----------|------------|--------------------------|
| `$front` | `''`       | `/`, `/about`            |
| `$auth`  | `'auth'`   | `/auth/login`            |
| `$app`   | `'app'`    | `/app/dashboard`         |
| `$admin` | `'admin'`  | `/admin/users`           |
| `$api`   | `'api/v1'` | `/api/v1/users`          |

Modules read via `$p = config('Prefixes')`.

---

## Available Modules

| Module     | Status   | Description                                      |
|------------|----------|--------------------------------------------------|
| `Core`     | ✓ Done   | Framework kernel: controllers, models, helpers   |
| `Auth`     | ✓ Done   | Login, registration, logout, session management  |
| `Users`    | ✓ Done   | Admin user list, app profile editing             |
| `Settings` | ✓ Done   | Key-value application settings                   |
| `Billing`  | ✓ Done   | Subscription plans and payment processing        |
| `Content`  | ✓ Done   | Pages, posts, nested categories, tags, URL resolver, Editor.js |
| `Media`    | Planned  | File uploads with structured storage             |
| `Notify`   | Planned  | Notification dispatch (email, Telegram, etc.)    |

---

## Available Providers

| Provider   | Implements | Description              |
|------------|------------|--------------------------|
| `Stripe`   | Payment    | Stripe payments          |
| `Telegram` | Notify     | Telegram bot messages    |
| `Mailgun`  | Mail       | Mailgun transactional email |
| `S3`       | Storage    | AWS S3 file storage      |

---

## Global Helpers (`oneshot/Core/Helpers/oneshot.php`)

| Function              | Description                                    |
|-----------------------|------------------------------------------------|
| `signId(int $id)`     | Encode an ID to an opaque hash                 |
| `signedId(string $h)` | Decode hash back to ID (returns 0 if invalid)  |
| `l($data, $tag)`      | Append to `writable/logs/{tag}.log`            |
| `rds(string $key)`    | Acquire a file-based lock (parallel worker guard) |
| `__($key, $default)`  | Translate with fallback default string         |

---

## Adding a Module

1. Run the generator: `php spark make:module ModuleName`
2. Edit `modules/ModuleName/Config/Routes.php` — add your routes
3. Create controllers extending the correct context base
4. Create models extending `OneShot\Core\Models\Base`
5. Add migration: `php spark make:migration ModuleName CreateTable`
6. Update `modules/ModuleName/modulename.md`
7. Verify: `php spark routes`

---

## Adding a Provider

1. Create `providers/ProviderName/ProviderName.php`
2. Implement the target contract interface (`Payment`, `Notify`, `Mail`, or `Storage`)
3. Register via `app/Config/Services.php`
4. Use anywhere via `service('payment')` (or the appropriate service name)

See `.ai/skills/add-provider.md` for full example.

---

## Overriding a Library Module

**Override routes**: create `modules/Auth/Config/Routes.php` — replaces the library routes entirely.

**Override a single view**: place `app/Views/auth/front/login.php` — `render()` checks `app/Views/` first.

**Override a controller**: extend the library controller or register a different class for the same route in `app/Config/Routes.php` (first-match wins).
