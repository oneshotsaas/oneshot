# Module Development Rules

## Structure
- One context → flat `Controllers/` directory
- Two or more contexts → subdirectories: `Controllers/Front/`, `Controllers/Admin/`, `Controllers/Api/`
- Every module has a `modulename.md` file documenting its purpose, contexts, controllers, models, services, events

## Controllers
- Base module controller named after the module (e.g., `Auth` for Auth module)
- All other controllers extend the base module controller
- Initialize models in `initController()`, not constructors
- Use `$this->appendBC()` for breadcrumbs on every page

## Models
- Named after the entity they represent: `User`, `Post`, `Order`
- Use inherited methods from `Base`: `getAll()`, `getOne()`, `getById()`, `add()`, `addGet()`, `getOrAdd()`

## Services
- Only create a Service when controller → model direct call is insufficient (complex logic, multiple models, events)
- Services extend `OneShot\Core\Services\Base`

## Views
- In view files, use `render('Module::path/to/view')` instead of `view()` for module views
- `render()` resolves `Module::path` with override priority: `app/Views/` → `modules/` → `oneshot/`
- In controllers, `$this->render('Auth::front/login')` already uses `render()` internally

## Routes
- Defined in `ModuleName/Config/Routes.php`
- Read URL prefixes via `config('Prefixes')`
- Controller strings **must start with `\`** to avoid CI4 prepending `App\Controllers\`:
  ```php
  $r->get('login', '\OneShot\Auth\Controllers\Login::index', ['as' => 'auth.login']);
  ```
- Apply filters on route groups, not globally:
  ```php
  $routes->group($p->admin . '/resource', ['filter' => 'admin'], function ($r) { ... });
  ```

## Navigation

Sidebar navigation is driven by **`app/Config/Nav.php`** — never hardcoded in layout files.

- `$admin` array — items shown in the admin sidebar
- `$app` array — items shown in the app (user cabinet) sidebar

Each item:
```php
[
    'label' => 'Users',
    'route' => 'admin.users',      // named route passed to route_to()
    'icon'  => 'M17 20h5v-2...',   // SVG path d= string (24x24 stroke)
    'match' => '/admin/users',     // optional: URL substring for active detection
]
```

Divider:
```php
['divider' => 'Section title']
```

**To add nav items for a new module:** edit `app/Config/Nav.php` directly — append to `$admin` or `$app`:
```php
$this->admin[] = ['label' => 'Reports', 'route' => 'admin.reports', 'icon' => '...'];
$this->app[]   = ['label' => 'Billing',  'route' => 'app.billing',  'icon' => '...'];
```

## Override Mechanism
- To override a library module: create `modules/Auth/` with the same structure
- Loader registers `modules/` before `oneshot/`, so your version is picked up first
- To override a single view: place it at `app/Views/auth/front/login.php`
  (lowercase module name, mirrors the namespace path)
