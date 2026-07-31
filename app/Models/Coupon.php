<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

final class Coupon extends Model
{
    protected static string $table = 'coupons';

    public static function findValidByCode(string $code): ?array
    {
        $coupon = Database::one(
            "SELECT * FROM coupons
             WHERE code = :c AND is_active = 1
             AND (valid_from IS NULL OR valid_from <= CURDATE())
             AND (valid_until IS NULL OR valid_until >= CURDATE())",
            ['c' => $code]
        );

        if (!$coupon) {
            return null;
        }
        if ($coupon['max_uses'] !== null && (int) $coupon['used_count'] >= (int) $coupon['max_uses']) {
            return null;
        }
        return $coupon;
    }

    public static function incrementUsage(int $id): void
    {
        Database::query('UPDATE coupons SET used_count = used_count + 1 WHERE id = :id', ['id' => $id]);
    }
}
