<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Models\Coupon;
use App\Models\RoomType;
use PDOException;

/**
 * Booking engine.
 *
 * The original `roombooking.php` picked "the first row where Availability=1"
 * out of a per-category table, with no transaction and no locking — under
 * concurrent requests, two customers could both read Availability=1 for the
 * same slot before either UPDATE ran, double-booking the room. It also
 * required a hardcoded array of table names to loop through.
 *
 * Here, availability is computed from actual date-range overlaps against the
 * `bookings` table, and the room is selected + reserved inside a single
 * transaction using `SELECT ... FOR UPDATE` row locking so two concurrent
 * requests cannot claim the same physical room for overlapping dates.
 */
final class BookingService
{
    public const TAX_RATE_SETTING = 'tax_rate_percent';

    /**
     * @throws \RuntimeException if no room of this type is free for the requested dates
     */
    public function createRoomBooking(array $input, ?int $userId): array
    {
        $roomType = RoomType::findBySlug($input['room_type_slug']);
        if (!$roomType) {
            throw new \RuntimeException('Selected room type does not exist.');
        }

        $checkIn = $input['check_in'];
        $checkOut = $input['check_out'];
        $nights = $this->nightsBetween($checkIn, $checkOut);
        if ($nights < 1) {
            throw new \RuntimeException('Check-out date must be after check-in date.');
        }

        $pdo = Database::connection();

        try {
            $pdo->beginTransaction();

            // Lock a candidate room of this type that has no overlapping active booking.
            $stmt = $pdo->prepare(
                "SELECT r.id FROM rooms r
                 WHERE r.room_type_id = :type_id AND r.status = 'available'
                 AND r.id NOT IN (
                     SELECT b.room_id FROM bookings b
                     WHERE b.status IN ('pending','confirmed','paid','checked_in')
                     AND b.check_in < :check_out AND b.check_out > :check_in
                 )
                 ORDER BY r.id ASC
                 LIMIT 1
                 FOR UPDATE"
            );
            $stmt->execute([
                'type_id' => $roomType['id'],
                'check_out' => $checkOut,
                'check_in' => $checkIn,
            ]);
            $room = $stmt->fetch();

            if (!$room) {
                $pdo->rollBack();
                throw new \RuntimeException('No rooms of this type are available for the selected dates.');
            }

            $pricing = $this->calculatePricing((float) $roomType['base_price'], $nights, $input['extra_service_ids'] ?? [], $input['coupon_code'] ?? null);

            $ref = booking_reference('BK');
            $bookingId = \App\Core\Database::insert(
                'INSERT INTO bookings
                    (booking_ref, user_id, room_id, guest_name, guest_phone, guest_city, guests_count,
                     check_in, check_out, nights, room_rate_snapshot, services_total, tax_amount,
                     discount_amount, coupon_id, total_amount, status, payment_method, notes)
                 VALUES
                    (:ref, :user_id, :room_id, :guest_name, :guest_phone, :guest_city, :guests_count,
                     :check_in, :check_out, :nights, :rate, :services_total, :tax_amount,
                     :discount_amount, :coupon_id, :total_amount, :status, :payment_method, :notes)',
                [
                    'ref' => $ref,
                    'user_id' => $userId,
                    'room_id' => $room['id'],
                    'guest_name' => $input['guest_name'],
                    'guest_phone' => $input['guest_phone'],
                    'guest_city' => $input['guest_city'] ?? null,
                    'guests_count' => (int) $input['guests_count'],
                    'check_in' => $checkIn,
                    'check_out' => $checkOut,
                    'nights' => $nights,
                    'rate' => $roomType['base_price'],
                    'services_total' => $pricing['services_total'],
                    'tax_amount' => $pricing['tax_amount'],
                    'discount_amount' => $pricing['discount_amount'],
                    'coupon_id' => $pricing['coupon_id'],
                    'total_amount' => $pricing['total'],
                    'status' => 'pending',
                    'payment_method' => $input['payment_method'] ?? 'pay_at_hotel',
                    'notes' => $input['notes'] ?? null,
                ]
            );

            foreach ($pricing['services'] as $service) {
                $pdo->prepare(
                    'INSERT INTO booking_extra_services (booking_id, extra_service_id, quantity, price_snapshot)
                     VALUES (:b, :s, 1, :p)'
                )->execute(['b' => $bookingId, 's' => $service['id'], 'p' => $service['price']]);
            }

            if ($pricing['coupon_id']) {
                Coupon::incrementUsage((int) $pricing['coupon_id']);
            }

            $pdo->commit();

            return ['booking_id' => (int) $bookingId, 'booking_ref' => $ref, 'total' => $pricing['total']];
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            \App\Core\Logger::error('Booking transaction failed: ' . $e->getMessage());
            throw new \RuntimeException('Could not complete the booking due to a system error. Please try again.');
        }
    }

    public function nightsBetween(string $checkIn, string $checkOut): int
    {
        $in = new \DateTime($checkIn);
        $out = new \DateTime($checkOut);
        return (int) $in->diff($out)->days;
    }

    /**
     * Server-side price calculation — never trust a total submitted by the client.
     * The original project accepted `Total` directly from $_POST and stored it
     * verbatim, meaning a booking's price could be tampered with client-side.
     */
    public function calculatePricing(float $baseRate, int $nights, array $extraServiceIds, ?string $couponCode): array
    {
        $roomSubtotal = $baseRate * $nights;

        $services = [];
        $servicesTotal = 0.0;
        if (!empty($extraServiceIds)) {
            $placeholders = implode(',', array_fill(0, count($extraServiceIds), '?'));
            $rows = Database::query(
                "SELECT id, name, price FROM extra_services WHERE is_active = 1 AND id IN ({$placeholders})",
                array_map('intval', $extraServiceIds)
            )->fetchAll();
            foreach ($rows as $row) {
                $services[] = $row;
                $servicesTotal += (float) $row['price'];
            }
        }

        $subtotal = $roomSubtotal + $servicesTotal;

        $discount = 0.0;
        $couponId = null;
        if ($couponCode) {
            $coupon = Coupon::findValidByCode($couponCode);
            if ($coupon) {
                $couponId = (int) $coupon['id'];
                $discount = $coupon['discount_type'] === 'percent'
                    ? $subtotal * ((float) $coupon['discount_value'] / 100)
                    : (float) $coupon['discount_value'];
                $discount = min($discount, $subtotal);
            }
        }

        $taxable = $subtotal - $discount;
        $taxRate = (float) setting(self::TAX_RATE_SETTING, 0);
        $tax = $taxable * ($taxRate / 100);

        $total = round($taxable + $tax, 2);

        return [
            'room_subtotal' => round($roomSubtotal, 2),
            'services' => $services,
            'services_total' => round($servicesTotal, 2),
            'discount_amount' => round($discount, 2),
            'coupon_id' => $couponId,
            'tax_amount' => round($tax, 2),
            'total' => $total,
        ];
    }
}
