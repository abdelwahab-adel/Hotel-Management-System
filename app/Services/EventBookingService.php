<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Models\EventType;
use PDOException;

/**
 * Event/venue booking engine (conference hall, banquet, etc).
 *
 * The original `booking.php` inserted every event request into `events`
 * with no availability check whatsoever — two customers could book the
 * same conference hall for the same overlapping time slot. Here, a
 * transaction + time-range overlap check prevents that, mirroring the
 * room-booking safeguards in BookingService.
 */
final class EventBookingService
{
    public function createEventBooking(array $input, ?int $userId): array
    {
        $eventType = EventType::find((int) $input['event_type_id']);
        if (!$eventType) {
            throw new \RuntimeException('Selected event type does not exist.');
        }

        $pdo = Database::connection();

        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare(
                "SELECT id FROM event_bookings
                 WHERE event_type_id = :type_id
                 AND event_date = :date
                 AND status IN ('pending','confirmed','paid')
                 AND start_time < :end_time AND end_time > :start_time
                 FOR UPDATE"
            );
            $stmt->execute([
                'type_id' => $eventType['id'],
                'date' => $input['event_date'],
                'end_time' => $input['end_time'],
                'start_time' => $input['start_time'],
            ]);

            if ($stmt->fetch()) {
                $pdo->rollBack();
                throw new \RuntimeException('This venue is already booked for an overlapping time on that date. Please choose another slot.');
            }

            $total = (float) $eventType['base_price'];

            $ref = booking_reference('EV');
            $id = Database::insert(
                'INSERT INTO event_bookings
                    (booking_ref, user_id, event_type_id, guest_name, guest_phone, guest_city,
                     guests_count, event_date, start_time, end_time, total_amount, status, notes)
                 VALUES
                    (:ref, :user_id, :type_id, :name, :phone, :city, :guests, :date, :start, :end, :total, :status, :notes)',
                [
                    'ref' => $ref,
                    'user_id' => $userId,
                    'type_id' => $eventType['id'],
                    'name' => $input['guest_name'],
                    'phone' => $input['guest_phone'],
                    'city' => $input['guest_city'] ?? null,
                    'guests' => (int) $input['guests_count'],
                    'date' => $input['event_date'],
                    'start' => $input['start_time'],
                    'end' => $input['end_time'],
                    'total' => $total,
                    'status' => 'pending',
                    'notes' => $input['notes'] ?? null,
                ]
            );

            $pdo->commit();

            return ['booking_id' => (int) $id, 'booking_ref' => $ref, 'total' => $total];
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            \App\Core\Logger::error('Event booking transaction failed: ' . $e->getMessage());
            throw new \RuntimeException('Could not complete the booking due to a system error. Please try again.');
        }
    }
}
