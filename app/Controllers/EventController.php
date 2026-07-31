<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Session;
use App\Core\Validator;
use App\Models\EventBooking;
use App\Models\EventType;
use App\Services\EventBookingService;
use App\Services\NotificationService;

final class EventController extends Controller
{
    public function show(): string
    {
        $eventTypes = EventType::where('is_active', 1);
        return $this->view('events.create', ['eventTypes' => $eventTypes]);
    }

    public function store(): string
    {
        $data = [
            'event_type_id' => (int) $this->input('event_type_id', 0),
            'guest_name'    => $this->input('guest_name', ''),
            'guest_phone'   => $this->input('guest_phone', ''),
            'guest_city'    => $this->input('guest_city', ''),
            'guests_count'  => $this->input('guests_count', 1),
            'event_date'    => $this->input('event_date', ''),
            'start_time'    => $this->input('start_time', ''),
            'end_time'      => $this->input('end_time', ''),
            'notes'         => $this->input('notes', ''),
        ];

        $v = new Validator($data);
        $v->required('event_type_id', 'Event type')
          ->required('guest_name', 'Full name')->maxLength('guest_name', 150, 'Full name')
          ->required('guest_phone', 'Phone number')->phone('guest_phone')
          ->required('event_date', 'Event date')->date('event_date', 'Event date')
          ->required('start_time', 'Start time')
          ->required('end_time', 'End time');

        if ($v->fails() || $data['end_time'] <= $data['start_time']) {
            Session::flash('errors', $v->fails() ? $v->errors() : ['end_time' => ['End time must be after start time.']]);
            Session::flash('_old', $data);
            return $this->redirect('/events');
        }

        try {
            $result = (new EventBookingService())->createEventBooking($data, Auth::id());
        } catch (\RuntimeException $e) {
            Session::flash('error', $e->getMessage());
            Session::flash('_old', $data);
            return $this->redirect('/events');
        }

        $user = Auth::user() ?? ['id' => null, 'full_name' => $data['guest_name'], 'email' => null];
        (new NotificationService())->bookingCreated($user, ['booking_ref' => $result['booking_ref'], 'total_amount' => $result['total']]);
        log_activity('event_booking_created', 'Event booking ' . $result['booking_ref']);

        Session::flash('success', 'Your event booking request (' . $result['booking_ref'] . ') has been received.');
        return $this->redirect('/events');
    }
}
