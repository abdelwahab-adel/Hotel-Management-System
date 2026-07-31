<?php

declare(strict_types=1);

namespace App\Services\Payment;

/**
 * STUB: structured for a real Stripe integration later.
 *
 * To activate: `composer require stripe/stripe-php`, set STRIPE_SECRET_KEY
 * in .env, and replace the body of charge() with a real
 * \Stripe\PaymentIntent::create(...) call. No other file needs to change —
 * PaymentGatewayFactory and the booking flow already route to this class
 * whenever payment_method = 'stripe'.
 */
final class StripeGateway implements PaymentGatewayInterface
{
    public function label(): string
    {
        return 'Credit / Debit Card (Stripe)';
    }

    public function charge(float $amount, string $currency, array $meta): array
    {
        throw new \RuntimeException('Stripe is not configured yet. Add your API key in .env and implement StripeGateway::charge().');
    }
}
