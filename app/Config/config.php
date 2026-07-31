<?php
/**
 * Loads .env into a simple config array. No Composer dependency required —
 * this is a ~20-line parser, not a reason to reach for vlucas/phpdotenv.
 */

declare(strict_types=1);

function load_env(string $path): void
{
    if (!is_file($path)) {
        return;
    }

    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        if (!str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        $value = trim($value, "\"'");
        if (getenv($key) === false) {
            putenv("{$key}={$value}");
            $_ENV[$key] = $value;
        }
    }
}

function env(string $key, mixed $default = null): mixed
{
    $value = $_ENV[$key] ?? getenv($key);
    if ($value === false || $value === null) {
        return $default;
    }
    return match (strtolower((string) $value)) {
        'true', '(true)' => true,
        'false', '(false)' => false,
        'null', '(null)' => null,
        default => $value,
    };
}

load_env(dirname(__DIR__, 2) . '/.env');

return [
    'app' => [
        'name'  => env('APP_NAME', 'The Pacific Hotel'),
        'env'   => env('APP_ENV', 'production'),
        'debug' => env('APP_DEBUG', false),
        'url'   => env('APP_URL', 'http://localhost'),
        'key'   => env('APP_KEY', ''), // used to key-derive CSRF/session secrets
    ],
    'db' => [
        'host'    => env('DB_HOST', '127.0.0.1'),
        'port'    => env('DB_PORT', '3306'),
        'name'    => env('DB_NAME', 'hotelmanagement'),
        'user'    => env('DB_USER', 'root'),
        'pass'    => env('DB_PASS', ''),
        'charset' => 'utf8mb4',
    ],
    'mail' => [
        'from_address' => env('MAIL_FROM_ADDRESS', 'no-reply@example.com'),
        'from_name'    => env('MAIL_FROM_NAME', 'The Pacific Hotel'),
        'smtp_host'    => env('MAIL_SMTP_HOST', ''),
        'smtp_port'    => env('MAIL_SMTP_PORT', 587),
        'smtp_user'    => env('MAIL_SMTP_USER', ''),
        'smtp_pass'    => env('MAIL_SMTP_PASS', ''),
    ],
    'session' => [
        'name'            => 'hms_session',
        'lifetime_minutes' => 120,
    ],
    'security' => [
        'login_max_attempts'   => 5,
        'login_lockout_minutes' => 15,
    ],
];
