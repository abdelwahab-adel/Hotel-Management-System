<?php

declare(strict_types=1);

namespace App\Core;

final class Config
{
    private static ?array $items = null;

    private static function load(): array
    {
        if (self::$items === null) {
            self::$items = require dirname(__DIR__) . '/Config/config.php';
        }
        return self::$items;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $segments = explode('.', $key);
        $value = self::load();

        foreach ($segments as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }
            $value = $value[$segment];
        }

        return $value;
    }
}
