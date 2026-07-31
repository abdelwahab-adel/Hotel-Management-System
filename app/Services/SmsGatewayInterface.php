<?php

declare(strict_types=1);

namespace App\Services;

/**
 * STUB structure for SMS notifications (booking confirmations, OTP, etc).
 * Wire up Twilio/Vonage/a local SMS gateway by implementing this interface
 * and swapping the binding wherever SMS is dispatched.
 */
interface SmsGatewayInterface
{
    public function send(string $toPhone, string $message): bool;
}
