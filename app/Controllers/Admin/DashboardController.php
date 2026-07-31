<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\ActivityLog;
use App\Models\Booking;
use App\Services\ReportService;

final class DashboardController extends Controller
{
    public function index(): string
    {
        $reportService = new ReportService();

        return $this->view('admin.dashboard', [
            'summary' => $reportService->summaryCards(),
            'revenueByMonth' => $reportService->revenueByMonth(6),
            'topRoomTypes' => $reportService->topRoomTypes(5),
            'recentBookings' => Booking::withDetails('1', [], 'b.created_at DESC', 8),
            'recentActivity' => ActivityLog::recent(10),
        ], 'layouts.admin');
    }
}
