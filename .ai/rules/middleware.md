---
paths:
  - 'app/{Models,Livewire,Http/Middleware}/**'
---

# Middleware

## User roles: plain enum column, no permission package
Roles use a plain `role` string column on `users` (default `UserRole::User`), cast to `App\Enums\UserRole` (cases: Admin, User) — no spatie/laravel-permission or similar package is installed (decision: avoid new dependencies per CLAUDE.md without approval).

Check access with `User::isAdmin()`. Gate admin-only routes with the `admin` middleware alias (`App\Http\Middleware\EnsureUserIsAdmin`, registered in `bootstrap/app.php`) — see `routes/web.php` `usuarios` route and `App\Livewire\Users\Index` for the reference implementation (create/edit/view/delete users). If real multi-role/permission needs emerge later, revisit adding a package instead of growing this enum ad hoc.
