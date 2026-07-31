<?php

declare(strict_types=1);

namespace App\Services\Payment;

/** Default/fallback gateway: guest pays in person, staff confirms via admin panel. */
final class PayAtHotelGateway implements PaymentGatewayInterface
{
    public function label(): string
    {
        return 'Pay at Hotel';
    }

    public function charge(float $amount, string $currency, array $meta): array
    {
        return ['status' => 'pending', 'reference' => null];
    }
}
