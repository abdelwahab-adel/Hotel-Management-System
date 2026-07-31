<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class PaymentTransaction extends Model
{
    protected static string $table = 'payment_transactions';
}
