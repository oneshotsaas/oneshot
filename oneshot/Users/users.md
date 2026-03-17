# Users

User management for admin panel and user profile editing in the app context.

## Contexts
- Admin: /admin/users         — list and view all users
- Admin: /admin/users/{hash}  — view single user
- App:   /app/profile         — view and update own profile

## Controllers
- `Users`   — admin user list and detail view
- `Profile` — current user profile (app context)

## Models
- Reuses `OneShot\Auth\Models\User`

## Dependencies
- Requires: `OneShot\Auth\Models\User`
