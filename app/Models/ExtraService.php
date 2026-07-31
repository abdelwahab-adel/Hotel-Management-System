<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class ExtraService extends Model
{
    protected static string $table = 'extra_services';

    public static function active(): array
    {
        return self::where('is_active', 1);
    }
}
