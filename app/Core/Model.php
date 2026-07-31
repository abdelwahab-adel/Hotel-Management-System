<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Minimal Active-Record-ish base. Not a full ORM by design: this project
 * intentionally avoids pulling in an ORM package (would require Composer,
 * see README) while still keeping every query centralized, parameterized,
 * and out of the Controllers/Views.
 */
abstract class Model
{
    protected static string $table;
    protected static string $primaryKey = 'id';

    public static function find(int|string $id): ?array
    {
        return Database::one(
            'SELECT * FROM ' . static::$table . ' WHERE ' . static::$primaryKey . ' = :id',
            ['id' => $id]
        );
    }

    public static function all(string $orderBy = ''): array
    {
        $sql = 'SELECT * FROM ' . static::$table;
        if ($orderBy !== '') {
            $sql .= ' ORDER BY ' . $orderBy;
        }
        return Database::all($sql);
    }

    public static function where(string $column, mixed $value, string $operator = '='): array
    {
        $sql = 'SELECT * FROM ' . static::$table . " WHERE {$column} {$operator} :value";
        return Database::all($sql, ['value' => $value]);
    }

    public static function create(array $attributes): string
    {
        $columns = array_keys($attributes);
        $placeholders = array_map(fn ($c) => ':' . $c, $columns);

        $sql = sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            static::$table,
            implode(', ', $columns),
            implode(', ', $placeholders)
        );

        return Database::insert($sql, $attributes);
    }

    public static function update(int|string $id, array $attributes): bool
    {
        $sets = implode(', ', array_map(fn ($c) => "{$c} = :{$c}", array_keys($attributes)));
        $attributes['__id'] = $id;

        $sql = sprintf(
            'UPDATE %s SET %s WHERE %s = :__id',
            static::$table,
            $sets,
            static::$primaryKey
        );

        Database::query($sql, $attributes);
        return true;
    }

    public static function delete(int|string $id): bool
    {
        Database::query(
            'DELETE FROM ' . static::$table . ' WHERE ' . static::$primaryKey . ' = :id',
            ['id' => $id]
        );
        return true;
    }

    public static function count(string $where = '1', array $params = []): int
    {
        $row = Database::one('SELECT COUNT(*) AS c FROM ' . static::$table . ' WHERE ' . $where, $params);
        return (int) ($row['c'] ?? 0);
    }
}
