<?php

declare(strict_types=1);

use App\Core\Auth;
use App\Core\Config;
use App\Core\Session;
use App\Core\View;

/** Escape output for safe HTML rendering. */
function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

/** Render a view (used inside controllers and nested partials). */
function view(string $view, array $data = [], ?string $layout = 'layouts/app'): string
{
    return View::render($view, $data, $layout);
}

/** Build an absolute URL for an internal path, respecting APP_URL / subfolder installs. */
function url(string $path = ''): string
{
    $base = rtrim((string) Config::get('app.url', ''), '/');
    if ($base === '') {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $base = $scheme . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
    }
    return $base . '/' . ltrim($path, '/');
}

/** Build a URL to a public/assets file with a cache-busting mtime query string. */
function asset(string $path): string
{
    $full = dirname(__DIR__, 2) . '/public/' . ltrim($path, '/');
    $version = is_file($full) ? filemtime($full) : time();
    return url($path) . '?v=' . $version;
}

/** Retrieve an old input value after a failed form submission (repopulate forms). */
function old(string $key, string $default = ''): string
{
    $old = Session::getFlash('_old', []);
    return htmlspecialchars((string) ($old[$key] ?? $default), ENT_QUOTES, 'UTF-8');
}

/** Get + clear a flash message. */
function flash(string $key): mixed
{
    return Session::getFlash($key);
}

/** Format a decimal amount using the configured currency symbol. */
function money(float|string $amount): string
{
    $currency = setting('currency_symbol', '$');
    return $currency . number_format((float) $amount, 2);
}

/** Read a value from the `settings` key/value table, with an in-request cache. */
function setting(string $key, mixed $default = null): mixed
{
    static $cache = null;
    if ($cache === null) {
        $cache = [];
        foreach (\App\Core\Database::all('SELECT setting_key, setting_value FROM settings') as $row) {
            $cache[$row['setting_key']] = $row['setting_value'];
        }
    }
    return $cache[$key] ?? $default;
}

/** Generate a UUID v4 string (used for users.uuid, no ext-uuid dependency needed). */
function uuid4(): string
{
    $data = random_bytes(16);
    $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
    $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

/** Generate a short, human-friendly public booking reference like BK-8F3A2C1D. */
function booking_reference(string $prefix = 'BK'): string
{
    return $prefix . '-' . strtoupper(bin2hex(random_bytes(4)));
}

/** Log an admin/staff action to activity_logs. */
function log_activity(string $action, string $description = ''): void
{
    \App\Core\Database::query(
        'INSERT INTO activity_logs (user_id, action, description, ip_address) VALUES (:u, :a, :d, :ip)',
        [
            'u'  => Auth::id(),
            'a'  => $action,
            'd'  => $description,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
        ]
    );
}

/** Push an in-app notification to a user (shown in the notification bell). */
function notify_user(int $userId, string $title, string $body, ?string $url = null): void
{
    \App\Core\Database::query(
        'INSERT INTO notifications (user_id, title, body, url) VALUES (:u, :t, :b, :url)',
        ['u' => $userId, 't' => $title, 'b' => $body, 'url' => $url]
    );
}

function current_user_name(): string
{
    return (string) Session::get('user_name', 'Guest');
}

/** Reads the flashed validation-errors array once per request (safe to call from many partials). */
function form_errors(): array
{
    static $errors = null;
    if ($errors === null) {
        $errors = Session::getFlash('errors', []);
    }
    return $errors;
}

function field_error(string $field): ?string
{
    $errors = form_errors();
    return $errors[$field][0] ?? null;
}

function field_error_class(string $field): string
{
    return field_error($field) ? 'border-red-400' : '';
}
