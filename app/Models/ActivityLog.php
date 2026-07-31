<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

final class ActivityLog extends Model
{
    protected static string $table = 'activity_logs';

    public static function recent(int $limit = 30): array
    {
        return Database::all(
            'SELECT al.*, u.full_name FROM activity_logs al
             LEFT JOIN users u ON u.id = al.user_id
             ORDER BY al.created_at DESC LIMIT ' . (int) $limit
        );
    }
}
