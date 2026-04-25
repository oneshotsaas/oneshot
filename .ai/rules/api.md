# API Development Rules

## Controllers
- Place in `Controllers/Api/` inside the module — namespace `OneShot\{Module}\Controllers\Api`
- Extend `OneShot\Core\Controllers\Api`
- List public (no-token) methods in `protected array $public = ['method1', 'method2']`
- Override `checkToken()` for custom token validation logic
- Use `$this->ok($data)` and `$this->fail($message, $code)` — never raw `json_encode`
- Always return `ResponseInterface` from controller methods

## Generic CRUD
The base `Api` controller provides ready-made `index`, `show`, `update`, `delete` actions.
To use them, set these attributes in your controller — no method code needed:

```php
protected string $modelClass = MyModel::class;
protected string $resource   = 'item';               // singular key in response
protected string $resources  = 'items';              // plural key in response
protected array  $fields     = ['id', 'name', ...];  // fields to expose; never include password
protected array  $editable   = ['name', ...];        // fields allowed via update()
protected array  $creatable  = ['name', ...];        // optional fields allowed via store()
```

Override any action when custom logic is required.
`store` has no generic implementation — always write it manually when needed.

## Routes
Only GET and POST methods — no PATCH/PUT/DELETE.
Delete action uses `POST /{id}/delete` (resource first, action second).

```php
$routes->group($p->api . '/resource', ['filter' => 'api'], function ($r) {
    $r->get('/',              '\NS\Controllers\Api\Resource::index');
    $r->get('(:num)',         '\NS\Controllers\Api\Resource::show/$1');
    $r->post('/',             '\NS\Controllers\Api\Resource::store');
    $r->post('(:num)',        '\NS\Controllers\Api\Resource::update/$1');
    $r->post('(:num)/delete', '\NS\Controllers\Api\Resource::delete/$1');
});
```

## Response Format
```json
{ "success": true,  "data": {...} }
{ "success": false, "message": "...", "errors": {...} }
```

## Pagination
- **Always use `offset` + `limit` query params** — never `page`
- `limit` defaults to 20, max 100 (enforced by `$this->getLimit()`)
- `offset` defaults to 0 (enforced by `$this->getOffset()`)
- Response must include `total`, `limit`, `offset` alongside the resource array

```json
{
  "success": true,
  "data": {
    "users": [...],
    "total": 142,
    "limit": 20,
    "offset": 0
  }
}
```

## Security
- **Never expose password hashes** — exclude from `$fields`
- **IDs in API responses are real integer IDs** — signed hashes are for HTML URLs only
- **`role` must not be in `$editable` or `$creatable`** — privilege escalation risk; role changes go through admin UI only
- **Protect destructive actions** — override `delete()` to block deletion of privileged records when needed
- Use `core.not_found` and `core.nothing_to_update` i18n keys for generic error messages
- Input accepted as JSON body or POST form: `$this->request->getJSON(true) ?: $this->request->getPost()`

## Conventions
- 200 for success, 201 for created, 400 for bad input, 401 for unauthorized, 403 for forbidden, 404 for not found
