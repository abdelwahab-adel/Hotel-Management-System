# Project Structure & Request Flow

## How a request flows through the system

1. **`public/index.php`** is the single entry point. It requires
   `app/bootstrap.php` (autoloader, error handlers, session start), builds a
   `Router`, loads `routes/web.php` to register every route, then dispatches
   the current request.
2. **`App\Core\Router::dispatch()`** matches the request method + path
   against registered routes. For any `POST`, it verifies the CSRF token
   first — before any controller code runs. It then runs the route's
   middleware stack (e.g. `AuthMiddleware`, `AdminOnlyMiddleware`) in order.
3. **Middleware** (`app/Middleware/`) either lets the request continue or
   redirects/aborts (e.g. `AuthMiddleware` redirects guests to `/login`;
   role middleware renders a 403 page).
4. **Controllers** (`app/Controllers/`) read input via `$this->input()`,
   validate it with `App\Core\Validator`, delegate business logic to a
   **Service** class when the logic is non-trivial (booking creation,
   pricing, PDF generation, reports), and return rendered HTML via
   `$this->view()`.
5. **Services** (`app/Services/`) contain the actual business rules —
   availability checking, transactional booking creation, pricing
   calculation, PDF/CSV generation, notifications. Controllers stay thin;
   this is where you'd add new business logic (e.g. a loyalty points
   system) without touching routing or views.
6. **Models** (`app/Models/`) are thin data-access classes over
   `App\Core\Database` (a PDO wrapper) — CRUD plus a few purpose-built
   queries per model (e.g. `Booking::withDetails()` joins room/room-type
   data for the admin bookings list).
7. **Views** (`app/Views/`) are plain PHP templates. `App\Core\View::render()`
   renders the requested view, then wraps it in a layout (`layouts/app.php`
   for the public site, `layouts/admin.php` for the back office,
   `layouts/auth.php` for login/register). Shared chrome (navbar, footer,
   admin sidebar, notification bell) lives in `Views/partials/` and is
   `include`d by the layouts, not duplicated per page.

## Why no framework, and what's deliberately hand-rolled

This project avoids Composer packages entirely (see the "why native PHP"
note in the main README), which means the usual framework conveniences —
routing, an ORM, a templating engine, a DI container — are replaced with
minimal, auditable equivalents:

| Instead of | This project uses | Where |
|---|---|---|
| Laravel/Symfony router | ~70-line route matcher supporting `{param}` segments + per-route middleware | `App\Core\Router` |
| Eloquent/Doctrine ORM | A thin `Model` base class (find/all/where/create/update/delete) + hand-written queries for anything more specific | `App\Core\Model`, `app/Models/*` |
| Blade/Twig | Plain PHP templates with a layout-wrapping `View::render()` | `App\Core\View`, `app/Views/*` |
| A DI container | Services are instantiated directly where used (`new BookingService()`); there's no hidden container magic to trace through | `app/Services/*` |
| Composer autoloading | A ~15-line `spl_autoload_register` mapping `App\Foo\Bar` → `app/Foo/Bar.php` | `app/bootstrap.php` |

This trades away some framework convenience for the whole request lifecycle
being traceable by reading maybe 5 files, and for the project running with
nothing beyond PHP + MySQL — no `composer install`, no Node build step, no
version-locking of third-party packages to maintain.

## Adding a new feature — a worked example

Say you want to add a "loyalty points" feature that awards points on
`checked_out` bookings:

1. **Migration**: add a `loyalty_points` column to `users` (or a separate
   `loyalty_ledger` table) in a new file under `database/migrations/`.
2. **Service logic**: in `App\Controllers\Admin\BookingController::updateStatus()`,
   when the new status is `checked_out`, call a new
   `App\Services\LoyaltyService::awardPoints($booking)`.
3. **Service class**: create `app/Services/LoyaltyService.php` with the
   actual point-calculation logic — keep it out of the controller.
4. **View**: show the running point balance in `app/Views/profile/dashboard.php`.

No routing framework config, no service container bindings, no ORM
migrations DSL to learn — just PHP files in the layer they belong to.
