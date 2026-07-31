# Security

This document maps each class of vulnerability in the original project to
its specific fix in this rebuild, plus the security decisions that don't
correspond to a specific old bug.

## SQL Injection

**Before:** queries were built with direct string interpolation of
`$_POST`/`$_GET` values (and, in the room-booking flow, of *table names*
selected from an array based on user input).

**Now:** `App\Core\Database` only exposes parameterized methods
(`query()`, `one()`, `all()`, `insert()`) backed by PDO with
`PDO::ATTR_EMULATE_PREPARES => false` (real server-side prepared statements,
not PHP-side string substitution). Every query in every Model/Controller/
Service uses named or positional bound parameters — there is no code path
in this application that concatenates user input into SQL.

## Plaintext passwords

**Before:** the `signup` table stored passwords as-is; login compared them
with `==`.

**Now:** `App\Core\Auth::hashPassword()` uses `password_hash()` (bcrypt,
cost 12); `Auth::attempt()` verifies with `password_verify()`, which is
timing-attack resistant, unlike `==`/`===` string comparison.

## Hardcoded admin credentials

**Before:** `success.php` checked `$_POST['username'] == 'venky' &&
$_POST['password'] == '123'` directly in the page.

**Now:** there is no separate admin auth system. Every account — customer,
receptionist, admin, super admin — is a row in `users` with a role, checked
through the same `Auth` class and gated by role-specific middleware
(`App\Middleware\AdminOnlyMiddleware`, `StaffMiddleware`,
`SuperAdminMiddleware`).

## CSRF

**Before:** no form included any anti-CSRF token.

**Now:** `App\Core\Router::dispatch()` calls
`Csrf::verifyRequestOrFail()` for **every** POST route before it reaches a
controller — this is enforced centrally, not per-form, so a new POST route
can't accidentally ship without protection. Every form in `app/Views`
includes `<?= $csrf_field ?>`. A missing/invalid token returns HTTP 419 with
a clear message rather than silently failing or 500ing.

## XSS

**Before:** values from `$_POST`/`$_SESSION`/the database were echoed
directly into HTML with no escaping anywhere.

**Now:** the `e()` helper (`htmlspecialchars(..., ENT_QUOTES, 'UTF-8')`) is
used at every point user-influenced or database data is rendered into HTML
in the view layer. There is no `echo $variable` of untrusted data anywhere
in `app/Views` — grep for it if you want to verify.

## Session hijacking / fixation

**Before:** `session_start()` with PHP defaults; no regeneration on login.

**Now:** `App\Core\Session::start()` sets `httponly`, `samesite=Lax`, and
`secure` (automatically, when the request is HTTPS) on the session cookie,
plus an idle timeout. `Auth::login()` and `Auth::logout()` both call
`session_regenerate_id(true)`, so a session ID captured before
authentication is useless afterward.

## Double-booking / race conditions

**Before:** availability was "loop through rows where `Availability=1`,
pick the first one" with no transaction — two concurrent requests could both
read the same available slot before either write landed.

**Now:** `App\Services\BookingService::createRoomBooking()` (and the
equivalent `EventBookingService`) run inside a database transaction using
`SELECT ... FOR UPDATE` to lock candidate rows before booking them,
verified against actual check-in/check-out date overlaps rather than a
static flag. This was specifically tested during development by
programmatically filling every room of a type for one date range and
confirming the next booking attempt is correctly rejected rather than
silently overbooking.

## Price tampering

**Before:** the booking form submitted a `Total` field directly to the
server, which trusted and stored it as-is.

**Now:** `bookings.total_amount` / `event_bookings.total_amount` are only
ever computed server-side, in `BookingService::calculatePricing()`, from
`room_types.base_price`, `extra_services.price`, `coupons`, and
`settings.tax_rate_percent` — the client never has a way to influence the
stored price beyond choosing which room/dates/extras/coupon to request.

## Brute-force login

**Before:** unlimited login attempts.

**Now:** `Auth::attempt()` checks `login_attempts` (identifier =
username + IP) and rejects further attempts once the configured threshold
is hit within the lockout window (defaults: 5 attempts / 15 minutes, see
`App\Config\config.php` → `security`).

## User enumeration on password reset

**Now:** `AuthController::sendResetLink()` shows the identical "If that
email is registered..." message regardless of whether the email exists,
so the forgot-password flow can't be used to check which emails have
accounts.

## Information disclosure via errors

**Before:** database/PHP errors could print directly to the page (or the
app used `or die($mysqli_error)` patterns common in this era of tutorial
code).

**Now:** `app/bootstrap.php` installs a global exception/error handler.
With `APP_DEBUG=false` (the production default), all errors are logged to
`storage/logs/` and the visitor sees a generic branded error page — no
stack traces, queries, or file paths are ever exposed.

## Direct file access

**Before:** ~20 individual `.php` files were each independently
web-reachable (`admin.php`, `status.php`, `pdf.php`, ...), each with its own
(inconsistent, sometimes missing) auth checks.

**Now:** `public/index.php` is the only entry point; every other PHP file
lives outside the web root's reachable surface when hosted with
`DocumentRoot` pointed at `public/` (see `docs/INSTALL.md`). Routes are
defined centrally in `routes/web.php` with explicit middleware, so there is
one place to audit what's protected and how.
