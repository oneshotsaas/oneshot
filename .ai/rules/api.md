# API Development Rules

## Controllers
- Extend `OneShot\Core\Controllers\Api`
- List public (no-token) methods in `protected array $public = ['method1', 'method2']`
- Override `checkToken()` for custom token validation logic
- Use `$this->ok($data)` and `$this->fail($message, $code)` — never raw `json_encode`

## Routes
```php
$routes->group($p->api . '/resource', ['filter' => 'api'], function ($r) {
    $r->get('/',          'NS\Controllers\Api\Resource::index');
    $r->post('/',         'NS\Controllers\Api\Resource::store');
    $r->get('(:segment)', 'NS\Controllers\Api\Resource::show/$1');
});
```

## Response Format
```json
{ "success": true,  "data": {...} }
{ "success": false, "message": "...", "errors": {...} }
```

## Conventions
- Always return `ResponseInterface` from controller methods
- 200 for success, 201 for created, 400 for bad input, 401 for unauthorized, 404 for not found
- Paginate with standard `?page=` query param; include `total` and `per_page` in response data
