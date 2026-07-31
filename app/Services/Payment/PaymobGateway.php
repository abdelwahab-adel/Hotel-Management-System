<?php

declare(strict_types=1);

namespace App\Services\Payment;

/** STUB — see StripeGateway.php for the activation pattern (Paymob, popular in Egypt). */
final class PaymobGateway implements PaymentGatewayInterface
{
    public function label(): string
    {
        return 'Paymob';
    }

    public function charge(float $amount, string $currency, array $meta): array
    {
        throw new \RuntimeException('Paymob is not configured yet. Add your API key in .env and implement PaymobGateway::charge().');
    }
}
