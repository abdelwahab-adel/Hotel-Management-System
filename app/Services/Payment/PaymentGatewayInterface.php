<?php

declare(strict_types=1);

namespace App\Services\Payment;

/**
 * Common contract every payment gateway integration implements. The system
 * only ever talks to this interface, so plugging in a real Stripe/PayPal/
 * Paymob/Fawry integration later means writing one class — nothing in the
 * booking flow, controllers, or database schema needs to change
 * (see database `payment_transactions.gateway` ENUM and
 * `bookings.payment_method` which already enumerate all four).
 */
interface PaymentGatewayInterface
{
    /** Human-readable name shown on the checkout page. */
    public function label(): string;

    /**
     * Start a charge for the given amount. Returns a result array with at
     * least ['status' => 'pending'|'succeeded'|'failed', 'reference' => string|null].
     */
    public function charge(float $amount, string $currency, array $meta): array;
}
