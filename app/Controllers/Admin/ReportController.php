<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Services\ReportService;

final class ReportController extends Controller
{
    public function index(): string
    {
        $report = new ReportService();

        return $this->view('admin.reports.index', [
            'summary' => $report->summaryCards(),
            'revenueByMonth' => $report->revenueByMonth(12),
            'topRoomTypes' => $report->topRoomTypes(10),
        ], 'layouts.admin');
    }

    public function exportBookingsCsv(): string
    {
        $rows = Database::all(
            "SELECT b.booking_ref, b.guest_name, b.guest_phone, rt.name AS room_type, r.room_number,
                    b.check_in, b.check_out, b.nights, b.total_amount, b.status, b.created_at
             FROM bookings b
             JOIN rooms r ON r.id = b.room_id
             JOIN room_types rt ON rt.id = r.room_type_id
             ORDER BY b.created_at DESC"
        );

        (new ReportService())->exportCsv(
            'bookings-' . date('Y-m-d') . '.csv',
            ['Reference', 'Guest', 'Phone', 'Room Type', 'Room #', 'Check-in', 'Check-out', 'Nights', 'Total', 'Status', 'Created'],
            array_map('array_values', $rows)
        );
        exit;
    }

    public function exportCustomersCsv(): string
    {
        $rows = Database::all("SELECT full_name, username, email, phone, status, created_at FROM users WHERE role = 'customer' ORDER BY created_at DESC");

        (new ReportService())->exportCsv(
            'customers-' . date('Y-m-d') . '.csv',
            ['Full Name', 'Username', 'Email', 'Phone', 'Status', 'Joined'],
            array_map('array_values', $rows)
        );
        exit;
    }
}
