<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

final class Booking extends Model
{
    protected static string $table = 'bookings';

    public static function findByRef(string $ref): ?array
    {
        return Database::one('SELECT * FROM bookings WHERE booking_ref = :r', ['r' => $ref]);
    }

    public static function forUser(int $userId): array
    {
        return Database::all(
            'SELECT b.*, rt.name AS room_type_name, r.room_number
             FROM bookings b
             JOIN rooms r ON r.id = b.room_id
             JOIN room_types rt ON rt.id = r.room_type_id
             WHERE b.user_id = :u ORDER BY b.created_at DESC',
            ['u' => $userId]
        );
    }

    public static function withDetails(string $where = '1', array $params = [], string $orderBy = 'b.created_at DESC', int $limit = 0): array
    {
        $sql = "SELECT b.*, rt.name AS room_type_name, r.room_number, u.email AS user_email
                FROM bookings b
                JOIN rooms r ON r.id = b.room_id
                JOIN room_types rt ON rt.id = r.room_type_id
                LEFT JOIN users u ON u.id = b.user_id
                WHERE {$where}
                ORDER BY {$orderBy}";
        if ($limit > 0) {
            $sql .= ' LIMIT ' . $limit;
        }
        return Database::all($sql, $params);
    }

    public static function findWithDetails(int $id): ?array
    {
        $rows = self::withDetails('b.id = :id', ['id' => $id], 'b.id', 1);
        return $rows[0] ?? null;
    }
}
