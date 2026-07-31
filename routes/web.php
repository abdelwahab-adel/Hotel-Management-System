<?php

declare(strict_types=1);

use App\Controllers\Admin\BookingController as AdminBookingController;
use App\Controllers\Admin\CustomerController as AdminCustomerController;
use App\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Controllers\Admin\EventController as AdminEventController;
use App\Controllers\Admin\ReportController as AdminReportController;
use App\Controllers\Admin\RoomController as AdminRoomController;
use App\Controllers\Admin\SettingsController;
use App\Controllers\AuthController;
use App\Controllers\BookingController;
use App\Controllers\ContactController;
use App\Controllers\DashboardController;
use App\Controllers\EventController;
use App\Controllers\HomeController;
use App\Controllers\RoomController;
use App\Core\Router;
use App\Middleware\AdminOnlyMiddleware;
use App\Middleware\AuthMiddleware;
use App\Middleware\GuestMiddleware;
use App\Middleware\StaffMiddleware;
use App\Middleware\SuperAdminMiddleware;

/** @var Router $router */

// ---------------------------------------------------------------- Public --
$router->get('/', [HomeController::class, 'index']);
$router->get('/about', [HomeController::class, 'about']);
$router->get('/gallery', [HomeController::class, 'gallery']);
$router->get('/contact', [ContactController::class, 'show']);
$router->post('/contact', [ContactController::class, 'submit']);

$router->get('/rooms', [RoomController::class, 'index']);
$router->get('/rooms/{slug}', [RoomController::class, 'show']);
$router->get('/rooms/{slug}/book', [BookingController::class, 'showForm']);
$router->post('/booking/quote', [BookingController::class, 'quote']);
$router->post('/booking', [BookingController::class, 'store']);
$router->get('/booking/confirmation/{ref}', [BookingController::class, 'confirmation']);

$router->get('/events', [EventController::class, 'show']);
$router->post('/events', [EventController::class, 'store']);

// ------------------------------------------------------------------ Auth --
$router->get('/login', [AuthController::class, 'showLogin'], [GuestMiddleware::class]);
$router->post('/login', [AuthController::class, 'login'], [GuestMiddleware::class]);
$router->get('/register', [AuthController::class, 'showRegister'], [GuestMiddleware::class]);
$router->post('/register', [AuthController::class, 'register'], [GuestMiddleware::class]);
$router->get('/logout', [AuthController::class, 'logout']);
$router->get('/forgot-password', [AuthController::class, 'showForgotPassword'], [GuestMiddleware::class]);
$router->post('/forgot-password', [AuthController::class, 'sendResetLink'], [GuestMiddleware::class]);
$router->get('/reset-password/{token}', [AuthController::class, 'showResetPassword'], [GuestMiddleware::class]);
$router->post('/reset-password', [AuthController::class, 'resetPassword'], [GuestMiddleware::class]);

// ------------------------------------------------------- Customer (auth) --
$router->get('/dashboard', [DashboardController::class, 'index'], [AuthMiddleware::class]);
$router->get('/dashboard/bookings', [DashboardController::class, 'bookings'], [AuthMiddleware::class]);
$router->post('/dashboard/bookings/{ref}/cancel', [DashboardController::class, 'cancelBooking'], [AuthMiddleware::class]);
$router->get('/dashboard/bookings/{ref}/invoice', [DashboardController::class, 'invoice'], [AuthMiddleware::class]);
$router->get('/dashboard/profile', [DashboardController::class, 'showProfile'], [AuthMiddleware::class]);
$router->post('/dashboard/profile', [DashboardController::class, 'updateProfile'], [AuthMiddleware::class]);
$router->post('/dashboard/notifications/read', [DashboardController::class, 'markNotificationsRead'], [AuthMiddleware::class]);

// ------------------------------------------------------- Admin / Back-office --
$router->get('/admin', [AdminDashboardController::class, 'index'], [StaffMiddleware::class]);

$router->get('/admin/room-types', [AdminRoomController::class, 'index'], [AdminOnlyMiddleware::class]);
$router->get('/admin/room-types/create', [AdminRoomController::class, 'create'], [AdminOnlyMiddleware::class]);
$router->post('/admin/room-types', [AdminRoomController::class, 'store'], [AdminOnlyMiddleware::class]);
$router->post('/admin/room-types/{id}/toggle', [AdminRoomController::class, 'toggleActive'], [AdminOnlyMiddleware::class]);
$router->post('/admin/room-types/{id}/rooms', [AdminRoomController::class, 'addRoom'], [AdminOnlyMiddleware::class]);
$router->post('/admin/rooms/{id}/delete', [AdminRoomController::class, 'deleteRoom'], [AdminOnlyMiddleware::class]);

$router->get('/admin/bookings', [AdminBookingController::class, 'index'], [StaffMiddleware::class]);
$router->post('/admin/bookings/{ref}/status', [AdminBookingController::class, 'updateStatus'], [StaffMiddleware::class]);
$router->get('/admin/bookings/{ref}/invoice', [AdminBookingController::class, 'invoice'], [StaffMiddleware::class]);

$router->get('/admin/customers', [AdminCustomerController::class, 'index'], [StaffMiddleware::class]);
$router->get('/admin/customers/{id}', [AdminCustomerController::class, 'show'], [StaffMiddleware::class]);
$router->post('/admin/customers/{id}/toggle', [AdminCustomerController::class, 'toggleStatus'], [AdminOnlyMiddleware::class]);

$router->get('/admin/events', [AdminEventController::class, 'index'], [StaffMiddleware::class]);
$router->post('/admin/events/{ref}/status', [AdminEventController::class, 'updateStatus'], [StaffMiddleware::class]);
$router->post('/admin/event-types', [AdminEventController::class, 'storeType'], [AdminOnlyMiddleware::class]);

$router->get('/admin/reports', [AdminReportController::class, 'index'], [AdminOnlyMiddleware::class]);
$router->get('/admin/reports/export/bookings', [AdminReportController::class, 'exportBookingsCsv'], [AdminOnlyMiddleware::class]);
$router->get('/admin/reports/export/customers', [AdminReportController::class, 'exportCustomersCsv'], [AdminOnlyMiddleware::class]);

$router->get('/admin/settings', [SettingsController::class, 'index'], [SuperAdminMiddleware::class]);
$router->post('/admin/settings', [SettingsController::class, 'update'], [SuperAdminMiddleware::class]);
$router->get('/admin/staff', [SettingsController::class, 'staff'], [SuperAdminMiddleware::class]);
$router->post('/admin/staff', [SettingsController::class, 'storeStaff'], [SuperAdminMiddleware::class]);
$router->get('/admin/activity-logs', [SettingsController::class, 'activityLogs'], [SuperAdminMiddleware::class]);
