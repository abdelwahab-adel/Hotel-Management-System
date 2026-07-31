<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;

/**
 * Powers the Admin > Reports screen: aggregate stats plus CSV export.
 *
 * Note on "Excel Export": true .xlsx generation normally uses PhpSpreadsheet,
 * which is only available via Composer/Packagist — unreachable from this
 * sandbox's network allowlist (see README "Known limitations"). CSV export
 * is provided instead, which opens natively in Excel/Sheets; swapping in
 * PhpSpreadsheet later is a drop-in change inside exportCsv()'s call sites.
 */
final class ReportService
{
    public function revenueByMonth(int $months = 6): array
    {
        return Database::all(
            "SELECT DATE_FORMAT(created_at, '%Y-%m') AS month,
                    SUM(total_amount) AS revenue,
                    COUNT(*) AS bookings_count
             FROM bookings
             WHERE status IN ('paid','checked_in','checked_out')
             AND created_at >= DATE_SUB(CURDATE(), INTERVAL :m MONTH)
             GROUP BY month ORDER BY month ASC",
            ['m' => $months]
        );
    }

    public function occupancyRate(): float
    {
        $totalRooms = (int) (Database::one("SELECT COUNT(*) AS c FROM rooms WHERE status = 'available'")['c'] ?? 0);
        if ($totalRooms === 0) {
            return 0.0;
        }
        $occupiedToday = (int) (Database::one(
            "SELECT COUNT(*) AS c FROM bookings
             WHERE status IN ('confirmed','paid','checked_in')
             AND CURDATE() >= check_in AND CURDATE() < check_out"
        )['c'] ?? 0);

        return round(($occupiedToday / $totalRooms) * 100, 1);
    }

    public function topRoomTypes(int $limit = 5): array
    {
        return Database::all(
            "SELECT rt.name, COUNT(b.id) AS bookings_count, SUM(b.total_amount) AS revenue
             FROM bookings b
             JOIN rooms r ON r.id = b.room_id
             JOIN room_types rt ON rt.id = r.room_type_id
             WHERE b.status IN ('paid','checked_in','checked_out')
             GROUP BY rt.id ORDER BY bookings_count DESC LIMIT :l",
            ['l' => $limit]
        );
    }

    public function summaryCards(): array
    {
        $totalBookings = (int) (Database::one('SELECT COUNT(*) AS c FROM bookings')['c'] ?? 0);
        $totalRevenue = (float) (Database::one("SELECT COALESCE(SUM(total_amount),0) AS s FROM bookings WHERE status IN ('paid','checked_in','checked_out')")['s'] ?? 0);
        $totalCustomers = (int) (Database::one("SELECT COUNT(*) AS c FROM users WHERE role = 'customer'")['c'] ?? 0);
        $pendingBookings = (int) (Database::one("SELECT COUNT(*) AS c FROM bookings WHERE status = 'pending'")['c'] ?? 0);

        return [
            'total_bookings'   => $totalBookings,
            'total_revenue'    => $totalRevenue,
            'total_customers'  => $totalCustomers,
            'pending_bookings' => $pendingBookings,
            'occupancy_rate'   => $this->occupancyRate(),
        ];
    }

    /** Streams a CSV file directly to the browser. */
    public function exportCsv(string $filename, array $headers, array $rows): void
    {
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        $out = fopen('php://output', 'w');
        fputcsv($out, $headers);
        foreach ($rows as $row) {
            fputcsv($out, $row);
        }
        fclose($out);
    }
}
