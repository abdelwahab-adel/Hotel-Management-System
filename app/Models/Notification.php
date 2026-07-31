<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

final class Notification extends Model
{
    protected static string $table = 'notifications';

    public static function forUser(int $userId, int $limit = 10): array
    {
        return Database::all(
            'SELECT * FROM notifications WHERE user_id = :u ORDER BY created_at DESC LIMIT ' . (int) $limit,
            ['u' => $userId]
        );
    }

    public static function unreadCount(int $userId): int
    {
        return (int) (Database::one(
            'SELECT COUNT(*) AS c FROM notifications WHERE user_id = :u AND is_read = 0',
            ['u' => $userId]
        )['c'] ?? 0);
    }

    public static function markAllRead(int $userId): void
    {
        Database::query('UPDATE notifications SET is_read = 1 WHERE user_id = :u', ['u' => $userId]);
    }
}
