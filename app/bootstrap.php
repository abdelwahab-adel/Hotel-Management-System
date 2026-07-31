<?php

declare(strict_types=1);

use App\Core\Config;
use App\Core\Logger;
use App\Core\Session;

define('BASE_PATH', dirname(__DIR__));

// --- PSR-4-style autoloader for the App\ namespace, zero Composer needed ---
spl_autoload_register(function (string $class): void {
    if (!str_starts_with($class, 'App\\')) {
        return;
    }
    $relative = substr($class, strlen('App\\'));
    $path = BASE_PATH . '/app/' . str_replace('\\', '/', $relative) . '.php';
    if (is_file($path)) {
        require $path;
    }
});

require __DIR__ . '/Core/helpers.php';

// --- Error handling: never leak stack traces/SQL errors to visitors ---
$debug = (bool) Config::get('app.debug', false);
ini_set('display_errors', $debug ? '1' : '0');
error_reporting(E_ALL);

set_exception_handler(function (\Throwable $e) use ($debug): void {
    Logger::error($e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    http_response_code(500);
    if ($debug) {
        echo '<pre>' . htmlspecialchars((string) $e, ENT_QUOTES, 'UTF-8') . '</pre>';
    } else {
        echo view('errors/500', []);
    }
});

set_error_handler(function (int $severity, string $message, string $file, int $line): bool {
    if (!(error_reporting() & $severity)) {
        return false;
    }
    Logger::error("{$message} in {$file}:{$line}");
    if ($severity & (E_ERROR | E_USER_ERROR)) {
        http_response_code(500);
        echo view('errors/500', []);
        exit;
    }
    return true;
});

Session::start();
