<?php

namespace App\Http\Controllers;

use App\Models\RoomType;
use Illuminate\View\View;

class RoomController extends Controller
{
    public function index(): View
    {
        return view('rooms.index', ['rooms' => RoomType::active()->with('amenities')->get()]);
    }

    public function show(RoomType $roomType): View
    {
        abort_unless($roomType->is_active, 404);

        return view('rooms.show', [
            'room' => $roomType->load('amenities'),
            'others' => RoomType::active()->whereKeyNot($roomType->id)->take(3)->get(),
        ]);
    }
}
