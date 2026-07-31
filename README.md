# The Pacific Hotel — Hotel Management System

A complete rebuild of a legacy PHP hotel booking/admin system into a secure,
normalized, MVC-structured application — native PHP, no framework, **zero
Composer/npm dependencies required to run it**.

![PHP](https://img.shields.io/badge/PHP-8.1%2B-777BB4)
![License](https://img.shields.io/badge/License-MIT-blue)
![No framework](https://img.shields.io/badge/Framework-None%20(native%20MVC)-informational)

---

## Why this rebuild exists

The original project worked, but had the problems every quick PHP tutorial
project has: SQL built with string interpolation, plaintext passwords, a
hardcoded admin login, no CSRF protection, a booking system that could
double-book a room under concurrent requests, and 20+ individually
web-reachable `.php` files with duplicated boilerplate in each one.

This rebuild keeps the same business functionality — book rooms, book event
venues, manage everything from an admin panel — on a redesigned foundation.
See **[docs/SECURITY.md](docs/SECURITY.md)** for the specific vulnerability →
fix mapping, and **[docs/DATABASE.md](docs/DATABASE.md)** for the schema
redesign.

## A note on tech choice: why native PHP, not Laravel

Laravel (or any Composer-based stack) is arguably the more common choice for
a project like this today. It wasn't used here because installing it means
running `composer install` against packagist.org, and this project was built
inside a sandboxed environment without access to Packagist — so shipping a
Laravel app I couldn't actually install and test would mean handing you
unverified code. Everything in this repository has been **linted file-by-file
and smoke-tested against a live MariaDB instance** (registration, login,
booking creation, double-booking prevention, admin approval flow, PDF
invoice generation, CSV export, CSRF rejection, and role-based access all
exercised over real HTTP requests during development).

If you do want a Laravel version, this codebase is a clean reference for the
business logic and schema — the `Services/`, `database/migrations/`, and
`routes/web.php` layers map almost directly onto Laravel's Service classes,
migrations, and route files.

## Features

- **Public site**: Home, Rooms & Suites (list + detail + live price quote),
  Gallery, Events & Venues, About, Contact
- **Booking engine**: date-range availability, extra services, coupon codes,
  tax calculation — all computed server-side (never trusts a client-submitted
  total) and protected against double-booking via row-level locking
- **Accounts**: registration, login with rate-limiting/lockout, forgot/reset
  password (token-based, no user enumeration), customer dashboard with
  booking history, PDF invoices, and profile editing
- **Admin back office**, gated by role:
  - **Super Admin**: everything, plus Settings and Staff account management
  - **Admin**: Rooms, Bookings, Events, Customers, Reports
  - **Receptionist**: Bookings, Events, Customers (day-to-day operations)
  - Dashboard with live charts (Chart.js), revenue/occupancy stats, recent
    activity feed
  - Room type + physical room CRUD, booking status workflow (confirm → pay →
    check-in → check-out, or reject/cancel), event/venue management,
    customer management, CSV report export, PDF invoices, activity log
- **Payment gateway architecture**: `PaymentGatewayInterface` with a working
  "Pay at Hotel" implementation and structured stubs for Stripe/PayPal/
  Paymob/Fawry — see [Payment gateways](#payment-gateways-not-pre-wired) below
- **Security**: see [docs/SECURITY.md](docs/SECURITY.md)

## Quick start

```bash
# 1. Point your webserver's document root at /public (recommended), or use
#    the included .htaccess if the domain root is the project root.

# 2. Create the database and load schema + demo data
mysql -u root -e "CREATE DATABASE hotelmanagement CHARACTER SET utf8mb4;"
mysql -u root hotelmanagement < database/migrations/001_create_schema.sql
mysql -u root hotelmanagement < database/seeds/seed.sql   # optional demo data

# 3. Configure environment
cp .env.example .env
# edit .env with your DB credentials

# 4. Serve it (for local testing; use Apache/Nginx in production)
php -S localhost:8000 -t public
```

Full step-by-step instructions, including production webserver configs, are
in **[docs/INSTALL.md](docs/INSTALL.md)**.

### Demo accounts (seeded by `seed.sql` — change or remove in production)

| Role         | Username     | Password         |
|--------------|--------------|------------------|
| Super Admin  | `superadmin` | `Admin@12345`    |
| Admin        | `admin`      | `Admin@12345`    |
| Receptionist | `reception`  | `Reception@12345`|
| Customer     | `customer`   | `Customer@12345` |

## Project structure

```
public/               ← web root (ONLY this folder should be publicly exposed)
  index.php           ← single front controller — every request goes through here
  assets/              ← CSS, JS
  uploads/             ← user-uploaded room images

app/
  Config/              ← .env loader + config.php
  Core/                ← framework: Router, Database (PDO), Auth, Session,
                          Csrf, Validator, View, Model, Logger, helpers.php
  Middleware/           ← AuthMiddleware, GuestMiddleware, role-gated middleware
  Controllers/          ← one per feature area; Admin/ subfolder for back office
  Models/               ← thin data-access classes over the `Database` layer
  Services/             ← business logic: BookingService, EventBookingService,
                          ReportService, InvoicePdfService, MailService,
                          Payment/ (gateway interface + Stripe/PayPal/etc. stubs)
  Views/                ← plain-PHP templates, organized by feature, with
                          layouts/ + partials/ for the shared shell
  Libraries/fpdf/       ← vendored FPDF (MIT-style license, no Composer needed)

database/
  migrations/001_create_schema.sql   ← full normalized schema
  seeds/seed.sql                      ← demo data (room types, users, etc.)

routes/web.php          ← every route + its middleware, in one file
docs/                    ← INSTALL, DATABASE (ERD), SECURITY, STRUCTURE
```

See **[docs/STRUCTURE.md](docs/STRUCTURE.md)** for a deeper walkthrough of
how a request flows through the system.

## Payment gateways: not pre-wired

The database and UI already model **Stripe, PayPal, Paymob, and Fawry** as
payment methods, and `App\Services\Payment\PaymentGatewayFactory` routes to a
dedicated class per gateway. Only `PayAtHotelGateway` actually processes
anything today — the other four throw a clear "not configured yet" exception
rather than silently pretending to charge a card. This is intentional: wiring
real payment credentials requires you to create merchant accounts and decide
on your own PCI-compliance posture, which isn't something to fake in a demo.
To activate one, see the comment at the top of `StripeGateway.php` (same
pattern for the other three) — it's a single-class change, nothing else in
the booking flow needs to be touched.

## Known limitations (and why)

- **No Composer packages are used anywhere**, including for the payment
  gateways above and for Excel export (native **CSV** export is provided
  instead — it opens in Excel/Sheets natively; swapping in PhpSpreadsheet
  later for a true `.xlsx` is a contained change inside `ReportService`).
- **SMS notifications** are structured (`SmsGatewayInterface`) but not
  implemented — plug in Twilio/Vonage/a local gateway by implementing that
  interface.
- **Email** uses PHP's built-in `mail()` by default (same as the original
  project), routed through `MailService` so swapping to SMTP/PHPMailer later
  is a one-file change. `mail()` is unreliable on many hosts — SMTP is
  recommended for production.
- **Room/gallery images** are placeholder icons in the seeded data — the
  schema (`room_type_images`) and upload directory (`public/uploads/rooms`)
  are ready for real photos.

## License

MIT for the code in this repository. The vendored FPDF library
(`app/Libraries/fpdf`) carries its own permissive license — see
`app/Libraries/fpdf/license.txt`.
