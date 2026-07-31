<?php

declare(strict_types=1);

namespace App\Services;

/** Fan-out helper: in-app + email (+ SMS once configured) from one call site. */
final class NotificationService
{
    public function __construct(private MailService $mail = new MailService())
    {
    }

    public function bookingCreated(array $user, array $booking): void
    {
        if (!empty($user['id'])) {
            notify_user(
                (int) $user['id'],
                'Booking received',
                "Your booking {$booking['booking_ref']} is pending confirmation.",
                url('/dashboard/bookings')
            );
        }
        if (!empty($user['email'])) {
            $this->mail->send(
                $user['email'],
                'Booking Confirmation — ' . $booking['booking_ref'],
                "Hi {$user['full_name']},\n\nWe received your booking {$booking['booking_ref']}. " .
                "Total due: " . money($booking['total_amount']) . ".\n\nWe will confirm shortly.\n\nThank you."
            );
        }
    }

    public function bookingStatusChanged(array $user, string $ref, string $status): void
    {
        if (!empty($user['id'])) {
            notify_user((int) $user['id'], 'Booking updated', "Booking {$ref} is now {$status}.", url('/dashboard/bookings'));
        }
    }
}
