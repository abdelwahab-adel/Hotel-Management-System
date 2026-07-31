<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Session;
use App\Models\Booking;
use App\Models\EventBooking;
use App\Models\User;

final class CustomerController extends Controller
{
    public function index(): string
    {
        $page = max(1, (int) $this->input('page', 1));
        $search = $this->input('q', '');
        $result = User::paginate($page, 15, $search);

        return $this->view('admin.customers.index', [
            'customers' => $result['rows'],
            'total' => $result['total'],
            'page' => $page,
            'perPage' => 15,
            'search' => $search,
        ], 'layouts.admin');
    }

    public function show(string $id): string
    {
        $customer = User::find((int) $id);
        if (!$customer || $customer['role'] !== 'customer') {
            http_response_code(404);
            return $this->view('errors.404');
        }

        return $this->view('admin.customers.show', [
            'customer' => $customer,
            'bookings' => Booking::forUser((int) $id),
            'events' => EventBooking::forUser((int) $id),
        ], 'layouts.admin');
    }

    public function toggleStatus(string $id): string
    {
        $customer = User::find((int) $id);
        if ($customer && $customer['role'] === 'customer') {
            $newStatus = $customer['status'] === 'active' ? 'suspended' : 'active';
            User::update((int) $id, ['status' => $newStatus]);
            log_activity('customer_status_toggled', "{$customer['username']} -> {$newStatus}");
            Session::flash('success', 'Customer status updated.');
        }
        return $this->redirect('/admin/customers');
    }
}
