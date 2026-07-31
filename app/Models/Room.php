<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

final class Room extends Model
{
    protected static string $table = 'rooms';

    public static function byType(int $roomTypeId): array
    {
        return Database::all('SELECT * FROM rooms WHERE room_type_id = :t ORDER BY room_number', ['t' => $roomTypeId]);
    }
}
