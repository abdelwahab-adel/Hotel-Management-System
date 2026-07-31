<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Session;
use App\Models\Booking;
use App\Services\NotificationService;

final class BookingController extends Controller
{
    private const VALID_TRANSITIONS = [
        'confirm'   => 'confirmed',
        'pay'       => 'paid',
        'reject'    => 'rejected',
        'cancel'    => 'cancelled',
        'check_in'  => 'checked_in',
        'check_out' => 'checked_out',
    ];

    public function index(): string
    {
        $status = $this->input('status', '');
        $search = $this->input('q', '');

        $where = '1';
        $params = [];
        if ($status !== '') {
            $where .= ' AND b.status = :status';
            $params['status'] = $status;
        }
        if ($search !== '') {
            $where .= ' AND (b.guest_name LIKE :s1 OR b.booking_ref LIKE :s2 OR b.guest_phone LIKE :s3)';
            $params['s1'] = $params['s2'] = $params['s3'] = "%{$search}%";
        }

        $bookings = Booking::withDetails($where, $params);

        return $this->view('admin.bookings.index', [
            'bookings' => $bookings,
            'status' => $status,
            'search' => $search,
        ], 'layouts.admin');
    }

    public function updateStatus(string $ref): string
    {
        $action = $this->input('action', '');
        if (!isset(self::VALID_TRANSITIONS[$action])) {
            Session::flash('error', 'Unknown action.');
            return $this->redirect('/admin/bookings');
        }

        $booking = Booking::findByRef($ref);
        if (!$booking) {
            Session::flash('error', 'Booking not found.');
            return $this->redirect('/admin/bookings');
        }

        $newStatus = self::VALID_TRANSITIONS[$action];
        Booking::update($booking['id'], ['status' => $newStatus]);
        log_activity('booking_status_changed', "Booking {$ref} -> {$newStatus}");

        if ($booking['user_id']) {
            $user = \App\Models\User::find($booking['user_id']);
            if ($user) {
                (new NotificationService())->bookingStatusChanged($user, $ref, $newStatus);
            }
        }

        Session::flash('success', "Booking {$ref} updated to " . str_replace('_', ' ', $newStatus) . '.');
        return $this->redirect('/admin/bookings');
    }

    public function invoice(string $ref): string
    {
        $bookingRow = Booking::findByRef($ref);
        if (!$bookingRow) {
            http_response_code(404);
            return $this->view('errors.404');
        }
        $booking = Booking::findWithDetails((int) $bookingRow['id']);
        $pdf = (new \App\Services\InvoicePdfService())->roomInvoice($booking);
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="invoice-' . $ref . '.pdf"');
        echo $pdf;
        exit;
    }
}
