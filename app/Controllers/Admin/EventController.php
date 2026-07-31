<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Session;
use App\Core\Validator;
use App\Models\EventBooking;
use App\Models\EventType;

final class EventController extends Controller
{
    private const VALID_TRANSITIONS = ['confirm' => 'confirmed', 'pay' => 'paid', 'reject' => 'rejected', 'cancel' => 'cancelled'];

    public function index(): string
    {
        $status = $this->input('status', '');
        $where = '1';
        $params = [];
        if ($status !== '') {
            $where = 'eb.status = :status';
            $params['status'] = $status;
        }

        return $this->view('admin.events.index', [
            'bookings' => EventBooking::withDetails($where, $params),
            'eventTypes' => EventType::all('id ASC'),
            'status' => $status,
        ], 'layouts.admin');
    }

    public function updateStatus(string $ref): string
    {
        $action = $this->input('action', '');
        if (!isset(self::VALID_TRANSITIONS[$action])) {
            return $this->redirect('/admin/events');
        }
        $booking = EventBooking::findByRef($ref);
        if ($booking) {
            EventBooking::update($booking['id'], ['status' => self::VALID_TRANSITIONS[$action]]);
            log_activity('event_status_changed', "Event {$ref} -> " . self::VALID_TRANSITIONS[$action]);
            Session::flash('success', 'Event booking updated.');
        }
        return $this->redirect('/admin/events');
    }

    public function storeType(): string
    {
        $data = [
            'name' => $this->input('name', ''),
            'description' => $this->input('description', ''),
            'base_price' => $this->input('base_price', 0),
        ];
        $v = new Validator($data);
        $v->required('name', 'Name')->numeric('base_price', 'Base price');
        if ($v->fails()) {
            Session::flash('error', 'Please provide a valid name and price.');
            return $this->redirect('/admin/events');
        }
        $data['is_active'] = 1;
        EventType::create($data);
        Session::flash('success', 'Venue/event type added.');
        return $this->redirect('/admin/events');
    }
}
