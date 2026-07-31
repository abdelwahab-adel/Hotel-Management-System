<?php

declare(strict_types=1);

namespace App\Services\Payment;

/** STUB — see StripeGateway.php for the activation pattern (Fawry Pay). */
final class FawryGateway implements PaymentGatewayInterface
{
    public function label(): string
    {
        return 'Fawry';
    }

    public function charge(float $amount, string $currency, array $meta): array
    {
        throw new \RuntimeException('Fawry is not configured yet. Add your merchant code in .env and implement FawryGateway::charge().');
    }
}
