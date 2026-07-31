<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

final class EventBooking extends Model
{
    protected static string $table = 'event_bookings';

    public static function findByRef(string $ref): ?array
    {
        return Database::one('SELECT * FROM event_bookings WHERE booking_ref = :r', ['r' => $ref]);
    }

    public static function forUser(int $userId): array
    {
        return Database::all(
            'SELECT eb.*, et.name AS event_type_name
             FROM event_bookings eb
             JOIN event_types et ON et.id = eb.event_type_id
             WHERE eb.user_id = :u ORDER BY eb.created_at DESC',
            ['u' => $userId]
        );
    }

    public static function withDetails(string $where = '1', array $params = [], string $orderBy = 'eb.created_at DESC'): array
    {
        return Database::all(
            "SELECT eb.*, et.name AS event_type_name
             FROM event_bookings eb
             JOIN event_types et ON et.id = eb.event_type_id
             WHERE {$where}
             ORDER BY {$orderBy}",
            $params
        );
    }
}
