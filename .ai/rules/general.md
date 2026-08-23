# General Development Rules

## Naming
- Classes: short and clear — `Login`, not `LoginController`; `User`, not `UserModel`
- Methods: short and clear — `index`, `store`, `show`, `update`, `destroy`
- Namespaces ensure uniqueness, so class names should not repeat the namespace

## Code Style
- No emojis anywhere — not in views, emails, comments, or strings
- DRY, SOLID, KISS — always
- No code in loops that hits the database; use `whereIn()` and post-process in PHP
- No hardcoded values; use `.env` and config files
- All dates: `DATETIME NULL` — no `TIMESTAMP`

## Database
- Prefix module tables: `modulename_tablename` (e.g., `auth_users`, `billing_plans`)
- All models extend `OneShot\Core\Models\Base`
- Work with DB only through models — no raw DB connections
- Use `save(['id' => $id, ...])` for updates; `getInsertID()` after insert

## Migrations
- Migration files live inside the module that owns them: `oneshot/{Module}/Database/Migrations/` or `modules/{Module}/Database/Migrations/`
- Every module namespace (`OneShot\{Module}`, `Modules\{Module}`) is registered statically in `app/Config/Autoload.php`, so migrations there are auto-discovered by CI4 — no special-casing needed
- Create: `php spark make:migration ModuleName Description`
- Run all: `php spark migrate --all`
- Run one module only: `php spark migrate -n "OneShot\\ModuleName"`
- Seed: `php spark db:seed DatabaseSeeder`

## Commands (spark)

- Spark commands live inside the module that owns them: `oneshot/{Module}/Commands/` or `modules/{Module}/Commands/`, namespace `OneShot\{Module}\Commands` / `Modules\{Module}\Commands`
- Every module namespace is registered statically in `app/Config/Autoload.php` — commands anywhere are auto-discovered by CI4, not just in Core
- If a command needs the oneshot helpers, call `helper('oneshot')` — `oneshot_helper.php` aliases the main file
- Group: use the module name to keep commands grouped in `php spark list`

## Security
- Never expose internal database IDs — use `signId()` / `signedId()`
- Validate at system boundaries only (user input, external APIs)
- Always escape output in views with `esc()` — never echo raw user-controlled data
- Always include `<?= csrf_field() ?>` in every HTML form
- Protect routes with auth filters on route groups only — never globally
- Use `service('throttler')` for rate-limiting on sensitive endpoints (login, register, API mutations)

## Logging
- Use `l($data, 'module_context')` for all logs
- In CLI mode: write to log only, no screen output

## Views — Forms

**Every admin form field MUST use this exact pattern. No exceptions.**

```php
<div class="grid grid-cols-[12rem_1fr] items-center gap-x-6">
    <span class="text-sm opacity-60">Label text</span>
    <input type="text" name="field" class="input input-sm input-bordered">
</div>
```

For textarea/multiline — use `items-start` and add `pt-1` to the label span:
```php
<div class="grid grid-cols-[12rem_1fr] items-start gap-x-6">
    <span class="text-sm opacity-60 pt-1">Label text</span>
    <textarea name="field" class="textarea textarea-sm textarea-bordered"></textarea>
</div>
```

For fields with a hint/description below the input — wrap input + hint in a `<div>`, use `items-start` + `pt-1` on label:
```php
<div class="grid grid-cols-[12rem_1fr] items-start gap-x-6">
    <span class="text-sm opacity-60 pt-1">Label text</span>
    <div>
        <input type="text" name="field" class="input input-sm input-bordered">
        <p class="text-xs opacity-40 mt-1">Hint text goes here</p>
    </div>
</div>
```

Rules:
- Label is always `<span class="text-sm opacity-60">` — never `<label>`, never `<label class="label">`
- Input size is always `input-sm` / `select-sm` / `textarea-sm`
- **NEVER** put hint text to the right of the input in the same row — always below the input in a `<p class="text-xs opacity-40 mt-1">`
- **NEVER** use `form-control`, `label-text`, DaisyUI `<label class="label">` — they break alignment
- **NEVER** use `grid-cols-2` for label+input — column widths become unequal and chaotic
- Wrap the entire form body in `<div class="card-body gap-4">` — each field row is a direct child

## Views — Page Actions & Filters (Topbar Slots)

The layout provides two slots rendered in the topbar — no standalone heading div in `<main>` ever.

**Everything goes in `page_actions_view` — always. Buttons, filters, or both.**

```php
// buttons only
$this->share('page_actions_view', 'Billing::admin/plans/_actions');

// filters only
$this->share('page_actions_view', 'Billing::app/usage/_filters');

// both — one partial that contains filter form + button together
$this->share('page_actions_view', 'Billing::admin/items/_actions');
```

`page_subbar_view` exists but is not used — ignore it.

**Partial naming convention:**
- `_actions.php` — one or more page-level buttons (`btn btn-primary btn-sm`, `btn btn-ghost btn-sm`)
- `_filters.php` — `<form method="get" class="flex items-center gap-2">` with fixed-width controls; no `flex-wrap`, no `mb-*`

**Rules:**
- **NEVER** put a standalone `<div class="mb-4 flex justify-end">` (or similar) at the top of a view just to hold a button — use `page_actions_view` instead
- **NEVER** put a `<form method="get">` filter block inside `<main>` — use `page_actions_view` instead
- Filter inputs must have fixed widths (`w-36`, `w-40`) so they don't stretch in the topbar flex container
- The partial receives all controller view variables automatically via `get_defined_vars()` — no need to re-pass them
- `page_actions` (raw HTML string) is still supported for backward compatibility but prefer `page_actions_view`

## Views — Page Titles
- **Never add `<h1>` to view files** inside admin or app layouts — breadcrumbs are the page title
- Set the breadcrumb in the controller via `$this->appendBC('Page Title', route_to('route'))` — that IS the title
- Only the front layout (landing pages) may use `<h1>` directly in views
- Exception: standalone `<h2>` section headings within a page's content are fine

## Views — Assets
- Module JS goes in `public/assets/{module}/{module}.js`, CSS in `public/assets/{module}/{module}.css`
- No inline `<script>` or `<style>` blocks in views — ever
- Dynamic init data passed via `data-*` attributes on the root element; the JS file reads them
- **Never add `<script>` tags inside view files** — all scripts are loaded centrally
- `oneshot/Core/Views/layouts/_scripts.php` is included before `</body>` in every layout (admin, app, front) — **this is the only place to add `<script>` tags**; add module scripts there so they can later be bundled into a single file
- **Shared/global JS** goes in `public/assets/core/core.js` — loaded automatically in all layouts via `Core::layouts/_scripts`
- Activate shared behaviours via CSS classes (e.g. `js-pw-toggle`), not via data attributes on layout elements

**Lazy / page-specific scripts** — for heavy scripts that should only load on one page (e.g. a rich text editor):

1. Add an `$extra_scripts` slot to `_scripts.php`:
   ```php
   <?php if (!empty($extra_scripts ?? '')): ?><?= $extra_scripts ?><?php endif ?>
   ```
2. Create a `_editorjs_scripts.php` (or similar) partial listing the `<script>` tags.
3. In the controller action that needs it, inject via `share()`:
   ```php
   $this->share('extra_scripts', render('Content::admin/items/_editorjs_scripts'));
   ```
   Use `render()` (the OneShot helper), **not** the native CI4 `view()` — `view()` does not understand the `Module::path` namespace syntax.
   Scripts are only loaded on pages where the controller explicitly calls `share('extra_scripts', ...)` — not globally.

## NEVER use `view()` — Always use `render()`

**`view()` is FORBIDDEN everywhere in OneShot controllers and views.**

- `view('Module::path/to/file')` — **WRONG**, throws `Invalid file` exception
- `render('Module::path/to/file')` — **CORRECT**, resolves `Module::` namespace syntax

This applies to all usages: `share()` calls, partial rendering, anywhere. There are zero exceptions.

## Views — i18n
- **Never hardcode UI strings in views** — always wrap in `__('key', 'Fallback text')`
- This applies to **every string visible to the user**, regardless of where it is defined: labels, hints, placeholders, button text, table column headers, empty-state messages, confirmation dialogs, tooltip `data-tip` values, badge text, modal titles — no exceptions.
- **Controllers are not exempt** — flash messages, validation errors, API response messages, and any other user-facing strings set in a controller must also use `__()`:
  ```php
  // Wrong:
  $this->redirectWith('route', 'error', 'Invalid credentials.');
  // Correct:
  $this->redirectWith('route', 'error', __('auth.invalid_credentials', 'Invalid credentials.'));
  ```
- Add every new key to the module's language file in the same change — never leave a key without an entry.
- Key format: `{module}.{key}` — e.g. `auth.email`, `core.login`, `users.save`
- `__()` does NOT support placeholders — use `sprintf()` for dynamic values:
  ```php
  // Wrong:
  __('billing.save_pct', 'Save :pct%', ['pct' => 20])   // outputs "Save :pct%"
  // Correct:
  sprintf(__('billing.save_pct', 'Save %d%%'), 20)       // outputs "Save 20%"
  ```
  Language file: `'save_pct' => 'Save %d%%'` (use `%s`, `%d`, `%%` — standard printf format)

## Module Helpers

- Place module-specific helper functions in `{Module}/Helpers/{module}.php`
- Guard every function with `if (!function_exists('fn_name'))` to allow app-level overrides
- Load with `helper('{module}')` — CI4 resolves from the module's registered namespace automatically
- Call `helper('{module}')` at the top of any controller action or view that uses the helpers (or in `initController`)
- Name helpers with a module prefix to avoid collisions: `content_slugify()`, not `slugify()`

## i18n — Language Files
Language files live inside the module that owns the strings, under `Language/en/{module}.php`.
CI4's `service('locator')->search()` auto-discovers them from all registered namespace paths — no extra registration needed.

| Prefix | File |
|--------|------|
| `auth.*` | `oneshot/Auth/Language/en/auth.php` |
| `users.*` | `oneshot/Users/Language/en/users.php` |
| `settings.*` | `oneshot/Settings/Language/en/settings.php` |
| `billing.*` | `oneshot/Billing/Language/en/billing.php` |
| `content.*` | `oneshot/Content/Language/en/content.php` |
| `core.*` | `oneshot/Core/Language/en/core.php` |
| `dashboard.*` | `modules/Dashboard/Language/en/dashboard.php` |
| new module | `{module_path}/Language/en/{module_prefix}.php` |

**Rules:**
- **Whenever you add or change any `__()` call — update the corresponding language file in the same change.** Never leave a key without an entry in its language file.
- New module: create `Language/en/{prefix}.php` alongside the first `__()` usage.
- Return a flat associative array: `['key' => 'English text']`.

## Settings — How to Register Module Settings

Settings are rows in the `settings` table. Modules add them via a seeder or migration.

**Minimal row:**
```php
[
    'key'   => 'billing.stripe_secret_key',   // {group}.{param}
    'value' => '',
    'type'  => 'text',                         // text|textarea|select|multiselect|color|url|code
    'label' => '',                             // leave empty — use language file instead (see below)
    'sort'  => 10,
]
```

**Translatable label and hint** — add to the module's language file (`{group}.php`):
```php
// key = {param} (the part after the dot in the setting key)
'stripe_secret_key'      => 'Stripe Secret Key',
'stripe_secret_key_hint' => 'Server-side secret from Stripe dashboard (sk_live_...).',
```
The Settings UI resolves them automatically:
- Label: `__('{group}.{param}', $db_label ?? $key)` — falls back to `label` column, then raw key
- Hint: `__('{group}.{param}_hint', '')` — shown below the field; hidden if key is absent

**Rules:**
- Always leave the `label` column empty and define the label in the language file instead
- Always add a `{param}_hint` key with a meaningful description of what the setting does and accepted values
- `select` type: populate `options` as JSON `[{"value":"x","label":"X"}, ...]`
- Secrets / tokens: use `type=text`; the value is AES-256-CTR encrypted at rest automatically

## Theming — DaisyUI
All theme CSS lives in `public/assets/css/themes/`:

| Pattern | Purpose |
|---|---|
| `{theme}.css` | DaisyUI base theme (light, dark, cupcake…) |
| `custom-{section}-{mode}.css` | Generated custom CSS override per section+mode |

- `_head.php` loads the active DaisyUI theme file, then the custom override on top
- Theme file path is sanitized to `[a-z0-9\-]` only — no whitelists needed
- DaisyUI CDN (`daisyui@5`) includes only `light` and `dark` — all other themes require a file in `themes/`
- `Setting::writeCssFile($section, $mode, $css)` writes to `themes/custom-{section}-{mode}.css`
- On the theme picker page: preload all theme CSS files so swatches render correctly

## Views — Tables

Every data table MUST follow this structure. No exceptions.

```php
<div class="rounded-lg border border-base-300 overflow-hidden">
    <table class="table table-sm w-full">
        <thead>
            <tr class="bg-base-200 text-xs uppercase tracking-wider opacity-70">
                <th class="font-semibold">Column</th>
                <th></th> <!-- actions column: no heading -->
            </tr>
        </thead>
        <tbody class="divide-y divide-base-200">
            <tr class="hover:bg-base-200/50 transition-colors">
                ...
            </tr>
        </tbody>
    </table>
</div>
```

Rules:
- **NEVER** use `table-zebra` — use `divide-y divide-base-200` instead
- Numbers: always `tabular-nums text-right`; secondary/dim numbers: add `opacity-50`
- Dates: `text-sm opacity-60 whitespace-nowrap`
- Empty cells: `<span class="opacity-30">—</span>`, not plain `—`
- Empty state row: `<td colspan="N" class="py-12 text-center text-sm opacity-40">message</td>`

**Action buttons — icon-only with tooltips:**
```php
<td>
    <div class="flex items-center justify-end gap-1">
        <!-- Edit: pencil icon, tooltip -->
        <div class="tooltip tooltip-left" data-tip="Edit">
            <a href="<?= route_to('...edit', signId($item->id)) ?>" class="btn btn-ghost btn-xs btn-square">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
            </a>
        </div>
        <!-- Delete: trash icon, ghost → red on hover -->
        <form method="post" action="<?= route_to('...delete', signId($item->id)) ?>" onsubmit="return confirm('Delete?')">
            <?= csrf_field() ?>
            <div class="tooltip tooltip-left" data-tip="Delete">
                <button class="btn btn-ghost btn-xs btn-square text-base-content/30 hover:text-error hover:bg-error/10 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                </button>
            </div>
        </form>
    </div>
</td>
```

- **Edit is always on the name** — the item name/title is a link to the edit page: `<a href="<?= route_to('...edit', ...) ?>" class="font-medium hover:opacity-70 transition-opacity">`
- **View** uses eye icon; **Edit** uses pencil; **Delete** uses trash
- Actions column: `justify-end`, `tooltip-left` so tooltips don't overflow right edge
- Delete button is NOT red by default — only turns `text-error bg-error/10` on hover

**Pager:** always `<?= $pager->links() ?>` — the template (`oneshot/Core/Views/pager/default_full.php`) hides itself when there is only one page.

## Links and Routing
- Always use `route_to('route.name')` for internal links
- Clean URLs: `/items/(:segment)`, not `/items/view/(:segment)`

**Catch-all routes** — if a module needs a catch-all `(:any)` route (e.g. a front-end URL resolver), two things are required:

1. Add `$routes->setPrioritize(true)` to `app/Config/Routes.php` (once, project-wide). This makes CI4 evaluate `(:any)` last regardless of module registration order.
2. Register the catch-all with a high priority value so it loses to all specific routes:
   ```php
   $routes->get('(:any)', '\OneShot\Content\Controllers\Front\Resolver::resolve/$1', ['priority' => 1000]);
   ```
   Priority `1000` = evaluated last. Specific routes default to priority `0`.

**Cache invalidation pattern** — when a module maintains a long-lived in-memory or file cache (e.g. a URL map), follow this pattern:
- Save with no TTL: `cache()->save($key, $data, 0)`
- Provide a static flush method called in every write operation (store/update/destroy):
  ```php
  public static function flushContentCache(): void
  {
      cache()->delete(config('Content')->cacheKey);
  }
  ```
- Never rely on TTL expiry for correctness — always flush explicitly on write.

## Views — Render Paths

The `render()` helper resolves `Module::path/to/view` by prepending `OneShot\` to the module name.
ц
**Correct** — module name only, no namespace prefix:
```php
$this->render('Billing::app/dashboard/index')   // → OneShot\Billing\Views/app/dashboard/index.php
$this->render('Auth::front/login')               // → OneShot\Auth\Views/front/login.php
```

**Wrong** — do NOT include the `OneShot\` prefix:
```php
$this->render('OneShot\Billing::app/dashboard/index')  // ❌ produces OneShot\OneShot\Billing\Views/...
```

Rule: the string before `::` is always the bare module name (`Billing`, `Auth`, `Users`, `Settings`, etc.).
