<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\RoomType;

final class HomeController extends Controller
{
    public function index(): string
    {
        $featuredRooms = array_slice(RoomType::activeWithAvailability(), 0, 3);
        return $this->view('home.index', ['featuredRooms' => $featuredRooms]);
    }

    public function about(): string
    {
        return $this->view('home.about');
    }

    public function gallery(): string
    {
        return $this->view('home.gallery');
    }
}
