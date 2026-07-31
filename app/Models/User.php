<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class User extends Model
{
    protected static string $table = 'users';

    public static function findByUsernameOrEmail(string $value): ?array
    {
        return \App\Core\Database::one(
            'SELECT * FROM users WHERE username = :v1 OR email = :v2 LIMIT 1',
            ['v1' => $value, 'v2' => $value]
        );
    }

    public static function usernameOrEmailExists(string $username, string $email): bool
    {
        $row = \App\Core\Database::one(
            'SELECT id FROM users WHERE username = :u OR email = :e LIMIT 1',
            ['u' => $username, 'e' => $email]
        );
        return $row !== null;
    }

    public static function paginate(int $page, int $perPage, string $search = ''): array
    {
        $offset = max(0, ($page - 1) * $perPage);
        $where = "role = 'customer'";
        $params = [];
        if ($search !== '') {
            $where .= ' AND (full_name LIKE :s1 OR email LIKE :s2 OR username LIKE :s3)';
            $params['s1'] = $params['s2'] = $params['s3'] = "%{$search}%";
        }

        $total = self::count($where, $params);
        $params['limit'] = $perPage;
        $params['offset'] = $offset;

        $rows = \App\Core\Database::all(
            "SELECT * FROM users WHERE {$where} ORDER BY created_at DESC LIMIT :limit OFFSET :offset",
            $params
        );

        return ['rows' => $rows, 'total' => $total];
    }
}
