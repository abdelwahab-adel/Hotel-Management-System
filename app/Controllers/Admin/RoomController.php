<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Session;
use App\Core\Validator;
use App\Models\Room;
use App\Models\RoomType;

final class RoomController extends Controller
{
    public function index(): string
    {
        $roomTypes = RoomType::all('sort_order ASC');
        foreach ($roomTypes as &$type) {
            $type['rooms'] = Room::byType((int) $type['id']);
        }
        return $this->view('admin.rooms.index', ['roomTypes' => $roomTypes], 'layouts.admin');
    }

    public function create(): string
    {
        return $this->view('admin.rooms.create', [], 'layouts.admin');
    }

    public function store(): string
    {
        $data = [
            'slug'        => strtolower(trim((string) $this->input('slug', ''))),
            'name'        => $this->input('name', ''),
            'description' => $this->input('description', ''),
            'base_price'  => $this->input('base_price', 0),
            'max_guests'  => $this->input('max_guests', 2),
            'bed_count'   => $this->input('bed_count', 1),
            'size_sqm'    => $this->input('size_sqm', null) ?: null,
        ];

        $v = new Validator($data);
        $v->required('slug', 'Slug')->alphaDash('slug', 'Slug')
          ->required('name', 'Name')->maxLength('name', 100, 'Name')
          ->required('base_price', 'Base price')->numeric('base_price', 'Base price')
          ->numeric('max_guests', 'Max guests')->numeric('bed_count', 'Bed count');

        if ($v->fails()) {
            Session::flash('errors', $v->errors());
            return $this->redirect('/admin/room-types/create');
        }

        $amenities = array_filter(array_map('trim', explode(',', (string) $this->input('amenities', ''))));
        $data['amenities_json'] = json_encode(array_values($amenities));
        $data['is_active'] = 1;

        $id = RoomType::create($data);

        $roomCount = max(0, (int) $this->input('initial_room_count', 0));
        for ($i = 1; $i <= $roomCount; $i++) {
            Room::create([
                'room_type_id' => $id,
                'room_number'  => strtoupper($data['slug']) . '-' . str_pad((string) $i, 3, '0', STR_PAD_LEFT),
            ]);
        }

        log_activity('room_type_created', $data['name']);
        Session::flash('success', 'Room type created.');
        return $this->redirect('/admin/room-types');
    }

    public function addRoom(string $id): string
    {
        $roomNumber = $this->input('room_number', '');
        if ($roomNumber === '') {
            Session::flash('error', 'Room number is required.');
            return $this->redirect('/admin/room-types');
        }
        Room::create(['room_type_id' => (int) $id, 'room_number' => $roomNumber]);
        log_activity('room_added', "Room {$roomNumber}");
        Session::flash('success', 'Room added.');
        return $this->redirect('/admin/room-types');
    }

    public function deleteRoom(string $id): string
    {
        Room::delete((int) $id);
        log_activity('room_deleted', "Room #{$id}");
        Session::flash('success', 'Room removed.');
        return $this->redirect('/admin/room-types');
    }

    public function toggleActive(string $id): string
    {
        $type = RoomType::find((int) $id);
        if ($type) {
            RoomType::update((int) $id, ['is_active' => $type['is_active'] ? 0 : 1]);
            log_activity('room_type_toggled', $type['name']);
        }
        return $this->redirect('/admin/room-types');
    }
}
