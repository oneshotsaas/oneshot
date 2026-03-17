# Auth

Handles user authentication: login, registration, and logout with session management.

## Contexts
- Front: /auth/login    — login form and processing
- Front: /auth/register — registration form and processing
- Front: /auth/logout   — session destruction

## Controllers
- `Auth`     — base controller, initializes AuthService
- `Login`    — login form (GET), credentials processing (POST), logout (GET)
- `Register` — registration form (GET), new user creation (POST)

## Models
- `User` — table `auth_users`, fields: id, email, password, name, role, lang, status

## Services
- `Auth::login($email, $password)` — validates credentials, creates session, returns result array
- `Auth::register($data)`          — creates user, triggers user.registered, returns result array
- `Auth::logout()`                 — destroys session
- `Auth::user()`                   — returns current user object from session

## Events
- `user.registered` — triggered after successful registration
- `user.login`      — triggered after successful login

## Migration
- `oneshot/Core/Database/Migrations/2026-01-01-000000_CreateAuthUsersTable.php`
- All migrations live in `oneshot/Core/Database/Migrations/` — never in module subdirectories

## Dependencies
- Uses: `OneShot\Auth\Models\User`
