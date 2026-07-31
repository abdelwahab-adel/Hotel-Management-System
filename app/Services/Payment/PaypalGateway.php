<?php

declare(strict_types=1);

namespace App\Services\Payment;

/** STUB — see StripeGateway.php for the activation pattern (PayPal Orders API). */
final class PaypalGateway implements PaymentGatewayInterface
{
    public function label(): string
    {
        return 'PayPal';
    }

    public function charge(float $amount, string $currency, array $meta): array
    {
        throw new \RuntimeException('PayPal is not configured yet. Add your client credentials in .env and implement PaypalGateway::charge().');
    }
}
