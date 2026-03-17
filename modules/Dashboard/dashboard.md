# Dashboard

Post-login landing pages for authenticated users, split by context (app/admin).

## Contexts
- App:   /app/    — user dashboard (`app.index`), accessible to `user` and `admin`
- Admin: /admin/  — admin dashboard (`admin.index`), accessible to `admin` only

## Controllers
- `Dashboard` — app dashboard index; passes `role` to view for role-aware content
- `Admin`     — admin dashboard index; entry point to admin panel

## Views
- `Dashboard::app/index`   — shows link to admin panel if role is `admin`, otherwise placeholder
- `Dashboard::admin/index` — admin entry page with links to management sections

## Roles
- `user`  — accesses /app/ only
- `admin` — accesses both /app/ and /admin/

## Dependencies
- Filter `auth`  on /app/   (requires login)
- Filter `admin` on /admin/ (requires role = admin)
- Layout `Core::layouts/app`   for app context
- Layout `Core::layouts/admin` for admin context
- Route `admin.users` used in admin view (provided by `oneshot/Users`)
