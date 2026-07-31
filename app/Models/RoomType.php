<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

final class RoomType extends Model
{
    protected static string $table = 'room_types';

    public static function activeWithAvailability(): array
    {
        // Availability = rooms of this type with no overlapping active booking today.
        return Database::all(
            "SELECT rt.*,
                    (SELECT COUNT(*) FROM rooms r WHERE r.room_type_id = rt.id AND r.status = 'available') AS total_rooms,
                    (SELECT COUNT(*) FROM rooms r
                        WHERE r.room_type_id = rt.id AND r.status = 'available'
                        AND r.id NOT IN (
                            SELECT b.room_id FROM bookings b
                            WHERE b.status IN ('pending','confirmed','paid','checked_in')
                            AND CURDATE() >= b.check_in AND CURDATE() < b.check_out
                        )
                    ) AS available_now
             FROM room_types rt
             WHERE rt.is_active = 1
             ORDER BY rt.sort_order ASC, rt.id ASC"
        );
    }

    public static function findBySlug(string $slug): ?array
    {
        return Database::one('SELECT * FROM room_types WHERE slug = :s', ['s' => $slug]);
    }

    public static function images(int $roomTypeId): array
    {
        return Database::all(
            'SELECT * FROM room_type_images WHERE room_type_id = :id ORDER BY is_primary DESC, sort_order ASC',
            ['id' => $roomTypeId]
        );
    }
}
