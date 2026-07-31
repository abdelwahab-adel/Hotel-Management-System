<?php

declare(strict_types=1);

namespace App\Services\Payment;

final class PaymentGatewayFactory
{
    public static function make(string $method): PaymentGatewayInterface
    {
        return match ($method) {
            'stripe' => new StripeGateway(),
            'paypal' => new PaypalGateway(),
            'paymob' => new PaymobGateway(),
            'fawry'  => new FawryGateway(),
            default  => new PayAtHotelGateway(),
        };
    }

    /** @return array<string,string> value => label, for building the checkout <select> */
    public static function available(): array
    {
        return [
            'pay_at_hotel' => 'Pay at Hotel',
            'stripe'       => 'Credit / Debit Card (Stripe) — coming soon',
            'paypal'       => 'PayPal — coming soon',
            'paymob'       => 'Paymob — coming soon',
            'fawry'        => 'Fawry — coming soon',
        ];
    }
}
