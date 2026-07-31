<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Core\Session;
use App\Core\Validator;
use App\Models\Booking;
use App\Models\EventBooking;
use App\Models\Notification;
use App\Models\User;
use App\Services\InvoicePdfService;

final class DashboardController extends Controller
{
    public function index(): string
    {
        $userId = Auth::id();
        $bookings = Booking::forUser($userId);
        $events = EventBooking::forUser($userId);

        return $this->view('profile.dashboard', [
            'user' => Auth::user(),
            'recentBookings' => array_slice($bookings, 0, 5),
            'recentEvents' => array_slice($events, 0, 5),
            'totalBookings' => count($bookings) + count($events),
        ]);
    }

    public function bookings(): string
    {
        $userId = Auth::id();
        return $this->view('profile.bookings', [
            'bookings' => Booking::forUser($userId),
            'events' => EventBooking::forUser($userId),
        ]);
    }

    public function cancelBooking(string $ref): string
    {
        $booking = Booking::findByRef($ref);
        if (!$booking || (int) $booking['user_id'] !== Auth::id()) {
            http_response_code(404);
            return $this->view('errors.404');
        }
        if (in_array($booking['status'], ['pending', 'confirmed'], true)) {
            Booking::update($booking['id'], ['status' => 'cancelled']);
            log_activity('booking_cancelled_by_customer', 'Booking ' . $ref);
            Session::flash('success', 'Booking cancelled.');
        } else {
            Session::flash('error', 'This booking can no longer be cancelled online.');
        }
        return $this->redirect('/dashboard/bookings');
    }

    public function invoice(string $ref): string
    {
        $booking = Booking::findWithDetails((int) (Booking::findByRef($ref)['id'] ?? 0));
        if (!$booking || (int) $booking['user_id'] !== Auth::id()) {
            http_response_code(404);
            return $this->view('errors.404');
        }

        $pdf = (new InvoicePdfService())->roomInvoice($booking);
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="invoice-' . $ref . '.pdf"');
        echo $pdf;
        exit;
    }

    public function showProfile(): string
    {
        return $this->view('profile.edit', ['user' => Auth::user()]);
    }

    public function updateProfile(): string
    {
        $data = [
            'full_name' => $this->input('full_name', ''),
            'phone'     => $this->input('phone', ''),
        ];

        $v = new Validator($data);
        $v->required('full_name', 'Full name')->maxLength('full_name', 150, 'Full name')->phone('phone');

        if ($v->fails()) {
            Session::flash('errors', $v->errors());
            return $this->redirect('/dashboard/profile');
        }

        User::update(Auth::id(), $data);
        Session::set('user_name', $data['full_name']);

        $newPassword = (string) $this->input('new_password', '');
        if ($newPassword !== '') {
            if (strlen($newPassword) < 8) {
                Session::flash('error', 'New password must be at least 8 characters.');
                return $this->redirect('/dashboard/profile');
            }
            User::update(Auth::id(), ['password_hash' => \App\Core\Auth::hashPassword($newPassword)]);
        }

        Session::flash('success', 'Profile updated.');
        return $this->redirect('/dashboard/profile');
    }

    public function markNotificationsRead(): string
    {
        Notification::markAllRead(Auth::id());
        return $this->json(['ok' => true]);
    }
}
