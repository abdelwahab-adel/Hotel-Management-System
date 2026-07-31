<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\ExtraService;
use App\Models\RoomType;

final class RoomController extends Controller
{
    public function index(): string
    {
        $roomTypes = RoomType::activeWithAvailability();
        return $this->view('rooms.index', ['roomTypes' => $roomTypes]);
    }

    public function show(string $slug): string
    {
        $roomType = RoomType::findBySlug($slug);
        if (!$roomType || !$roomType['is_active']) {
            http_response_code(404);
            return $this->view('errors.404');
        }

        $images = RoomType::images((int) $roomType['id']);
        $extraServices = ExtraService::active();

        return $this->view('rooms.show', [
            'roomType' => $roomType,
            'images' => $images,
            'extraServices' => $extraServices,
        ]);
    }
}
