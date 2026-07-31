# Installation Guide

## Requirements

- PHP 8.1 or newer, with the `pdo_mysql`, `mbstring`, and `session`
  extensions (all standard/enabled by default on virtually every host)
- MySQL 5.7+ or MariaDB 10.3+
- An Apache (with `mod_rewrite`) or Nginx webserver — or PHP's built-in
  server for local development
- **No Composer, no npm, no build step required.** Front-end libraries
  (Tailwind, Font Awesome, Google Fonts, Chart.js) load from public CDNs
  in the page `<head>` — see `app/Views/partials/head.php` if you need to
  self-host them for an offline/intranet deployment.

## 1. Get the files onto your server

Upload the whole project. Only the `public/` folder should ultimately be
reachable from the web — see [Document root](#document-root-recommended-setup)
below.

## 2. Create the database

```bash
mysql -u root -p -e "CREATE DATABASE hotelmanagement CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Create a dedicated app user (don't use root in production)
mysql -u root -p -e "
CREATE USER 'hms_user'@'localhost' IDENTIFIED BY 'choose_a_strong_password';
GRANT ALL PRIVILEGES ON hotelmanagement.* TO 'hms_user'@'localhost';
FLUSH PRIVILEGES;
"
```

Load the schema, then (optionally) the demo data:

```bash
mysql -u hms_user -p hotelmanagement < database/migrations/001_create_schema.sql
mysql -u hms_user -p hotelmanagement < database/seeds/seed.sql
```

> **Production tip:** skip `seed.sql` (or run it, then delete/change the demo
> accounts and coupon immediately). It's meant for evaluating the system, not
> for going live with its default logins.
>
> **A note if you connect over TCP (`127.0.0.1`) instead of a Unix socket:**
> MySQL/MariaDB installs on Debian/Ubuntu often restrict the `root` user to
> `auth_socket`/`unix_socket` authentication, which only works over the local
> socket, not TCP. The dedicated `hms_user` created above with a real
> password avoids this entirely — use it in `.env` rather than `root`.

## 3. Configure the environment

```bash
cp .env.example .env
```

Edit `.env`:

```ini
APP_NAME="Your Hotel Name"
APP_ENV=production
APP_DEBUG=false          # keep false in production — true prints stack traces
APP_URL=https://yourdomain.com

DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=hotelmanagement
DB_USER=hms_user
DB_PASS=choose_a_strong_password
```

`.env` is already listed in `.gitignore` and blocked from direct web access
by both `.htaccess` files (see [Security notes](#security-notes)) — never
commit it or make it web-reachable.

## 4. Document root (recommended setup)

Point your Apache/Nginx **document root directly at the `public/` folder**,
not the project root. This means `app/`, `database/`, `storage/`, `.env`,
etc. are simply outside the webserver's reach — the strongest possible
protection, stronger than relying on `.htaccess` rules.

**Apache** (virtual host):
```apache
<VirtualHost *:80>
    ServerName yourdomain.com
    DocumentRoot /var/www/hotel-management-system/public

    <Directory /var/www/hotel-management-system/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

**Nginx**:
```nginx
server {
    listen 80;
    server_name yourdomain.com;
    root /var/www/hotel-management-system/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\. {
        deny all;
    }
}
```

### If you can't change the document root (shared hosting)

If your host only lets you point the domain at the project's top-level
folder, the included root-level `.htaccess` rewrites every request into
`public/` automatically — no code changes needed. This works, but hosting
directly at `public/` is still the more robust option if it's available to
you, since it makes `app/`, `database/`, and `storage/` physically
unreachable rather than relying on rewrite rules.

## 5. File permissions

```bash
chmod -R 755 storage public/uploads
chown -R www-data:www-data storage public/uploads   # adjust user to your webserver
```

`storage/logs` needs to be writable for error logging;
`public/uploads/rooms` needs to be writable if you add room photo uploads.

## 6. Local development (no Apache/Nginx needed)

```bash
php -S localhost:8000 -t public
```

Then visit `http://localhost:8000`.

## 7. First login

Use one of the [demo accounts](../README.md#demo-accounts-seeded-by-seedsql--change-or-remove-in-production)
if you loaded `seed.sql`, or create a Super Admin manually:

```sql
INSERT INTO users (uuid, full_name, username, email, password_hash, role, status)
VALUES (
  UUID(),
  'Your Name',
  'youradmin',
  'you@yourhotel.com',
  -- Generate this hash with: php -r "echo password_hash('YourStrongPassword', PASSWORD_BCRYPT, ['cost'=>12]);"
  '$2y$12$REPLACE_WITH_A_REAL_BCRYPT_HASH',
  'super_admin',
  'active'
);
```

## Security notes

- Change or delete the demo accounts and `WELCOME10` coupon before going live.
- Set `APP_DEBUG=false` in production (default in `.env.example`) — with it
  `true`, uncaught exceptions print full stack traces to visitors.
- Serve the site over HTTPS. Session cookies automatically switch to
  `secure` when the request is HTTPS (see `App\Core\Session`), so this
  happens without extra configuration once your TLS certificate is in place.
- Consider moving from PHP's `mail()` to SMTP (see `App\Services\MailService`)
  for reliable delivery — `mail()` is frequently blocked/spam-filtered.
