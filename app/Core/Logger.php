<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Minimal file logger. The original project let PHP/MySQL errors print
 * straight to the page (or used `or die('...')`), which leaks stack traces
 * and DB details to visitors. Here, errors are logged to disk and the user
 * only ever sees a generic message.
 */
final class Logger
{
    private static function path(): string
    {
        $dir = dirname(__DIR__, 2) . '/storage/logs';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        return $dir . '/' . date('Y-m-d') . '.log';
    }

    private static function write(string $level, string $message): void
    {
        $line = sprintf('[%s] %s: %s%s', date('Y-m-d H:i:s'), $level, $message, PHP_EOL);
        @file_put_contents(self::path(), $line, FILE_APPEND | LOCK_EX);
    }

    public static function info(string $message): void
    {
        self::write('INFO', $message);
    }

    public static function warning(string $message): void
    {
        self::write('WARNING', $message);
    }

    public static function error(string $message): void
    {
        self::write('ERROR', $message);
    }
}
