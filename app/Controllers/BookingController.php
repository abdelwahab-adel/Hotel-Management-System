<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Session;
use App\Core\Validator;
use App\Models\Booking;
use App\Models\RoomType;
use App\Services\BookingService;
use App\Services\NotificationService;
use App\Services\Payment\PaymentGatewayFactory;

final class BookingController extends Controller
{
    public function showForm(string $slug): string
    {
        $roomType = RoomType::findBySlug($slug);
        if (!$roomType) {
            http_response_code(404);
            return $this->view('errors.404');
        }

        return $this->view('bookings.create', [
            'roomType' => $roomType,
            'extraServices' => \App\Models\ExtraService::active(),
            'gateways' => PaymentGatewayFactory::available(),
            'user' => Auth::user(),
        ]);
    }

    /** AJAX endpoint: live price preview as the guest picks dates/extras (no total is ever trusted from the client on submit). */
    public function quote(): string
    {
        $slug = (string) $this->input('room_type_slug', '');
        $checkIn = (string) $this->input('check_in', '');
        $checkOut = (string) $this->input('check_out', '');
        $extraIds = array_map('intval', (array) ($_POST['extra_service_ids'] ?? []));
        $coupon = $this->input('coupon_code', '') ?: null;

        $roomType = RoomType::findBySlug($slug);
        if (!$roomType) {
            return $this->json(['error' => 'Invalid room type'], 422);
        }

        $service = new BookingService();
        $nights = $service->nightsBetween($checkIn, $checkOut);
        if ($nights < 1) {
            return $this->json(['error' => 'Select a valid date range'], 422);
        }

        $pricing = $service->calculatePricing((float) $roomType['base_price'], $nights, $extraIds, $coupon);
        $pricing['nights'] = $nights;

        return $this->json($pricing);
    }

    public function store(): string
    {
        $data = [
            'room_type_slug'    => $this->input('room_type_slug', ''),
            'guest_name'        => $this->input('guest_name', ''),
            'guest_phone'       => $this->input('guest_phone', ''),
            'guest_city'        => $this->input('guest_city', ''),
            'guests_count'      => $this->input('guests_count', 1),
            'check_in'          => $this->input('check_in', ''),
            'check_out'         => $this->input('check_out', ''),
            'payment_method'    => $this->input('payment_method', 'pay_at_hotel'),
            'notes'             => $this->input('notes', ''),
            'coupon_code'       => $this->input('coupon_code', '') ?: null,
        ];
        $extraIds = array_map('intval', (array) ($_POST['extra_service_ids'] ?? []));

        $v = new Validator($data);
        $v->required('room_type_slug', 'Room type')
          ->required('guest_name', 'Full name')->maxLength('guest_name', 150, 'Full name')
          ->required('guest_phone', 'Phone number')->phone('guest_phone')
          ->required('check_in', 'Check-in date')->date('check_in', 'Check-in date')
          ->required('check_out', 'Check-out date')->date('check_out', 'Check-out date')
          ->in('payment_method', array_keys(\App\Services\Payment\PaymentGatewayFactory::available()), 'Payment method');

        if ($v->fails()) {
            Session::flash('errors', $v->errors());
            Session::flash('_old', $data);
            return $this->redirect('/rooms/' . $data['room_type_slug'] . '/book');
        }

        if ($data['check_out'] <= $data['check_in']) {
            Session::flash('error', 'Check-out date must be after check-in date.');
            return $this->redirect('/rooms/' . $data['room_type_slug'] . '/book');
        }

        $data['extra_service_ids'] = $extraIds;

        try {
            $service = new BookingService();
            $result = $service->createRoomBooking($data, Auth::id());
        } catch (\RuntimeException $e) {
            Session::flash('error', $e->getMessage());
            Session::flash('_old', $data);
            return $this->redirect('/rooms/' . $data['room_type_slug'] . '/book');
        }

        $user = Auth::user() ?? ['id' => null, 'full_name' => $data['guest_name'], 'email' => null];
        (new NotificationService())->bookingCreated($user, ['booking_ref' => $result['booking_ref'], 'total_amount' => $result['total']]);
        log_activity('booking_created', 'Room booking ' . $result['booking_ref']);

        return $this->redirect('/booking/confirmation/' . $result['booking_ref']);
    }

    public function confirmation(string $ref): string
    {
        $booking = Booking::findByRef($ref);
        if (!$booking) {
            http_response_code(404);
            return $this->view('errors.404');
        }
        return $this->view('bookings.confirmation', ['booking' => $booking]);
    }
}
