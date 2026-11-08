<?php

namespace App\Http\Controllers;

use App\Enums\BookingSource;
use App\Exceptions\RoomUnavailableException;
use App\Http\Requests\BookingQuoteRequest;
use App\Http\Requests\StoreBookingRequest;
use App\Models\Booking;
use App\Models\RoomType;
use App\Models\Setting;
use App\Services\BookingNotifier;
use App\Services\BookingService;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function __construct(private BookingService $bookings) {}

    public function create(Request $request): View
    {
        $rooms = RoomType::active()->get();
        $selected = $rooms->firstWhere('slug', $request->query('room'));

        return view('booking.create', [
            'rooms' => $rooms,
            'defaults' => [
                'room_type_id' => old('room_type_id', $selected?->id ?? $request->integer('room_type_id') ?: ''),
                'check_in' => old('check_in', $request->query('check_in', today()->toDateString())),
                'check_out' => old('check_out', $request->query('check_out', today()->addDay()->toDateString())),
                'rooms' => (int) old('rooms', $request->query('rooms', 1)),
                'adults' => (int) old('adults', $request->query('adults', 2)),
                'children' => (int) old('children', $request->query('children', 0)),
            ],
            'maxRooms' => (int) Setting::read('max_rooms_per_booking'),
        ]);
    }

    public function quote(BookingQuoteRequest $request): JsonResponse
    {
        $room = $request->roomType();
        $checkIn = CarbonImmutable::parse($request->input('check_in'));
        $checkOut = CarbonImmutable::parse($request->input('check_out'));
        $rooms = $request->integer('rooms');

        $available = $this->bookings->availableUnits($room, $checkIn, $checkOut);

        return response()->json([
            'available' => $available >= $rooms,
            'units_left' => max(0, $available),
            ...$this->bookings->quote($room, $checkIn, $checkOut, $rooms),
        ]);
    }

    public function store(StoreBookingRequest $request, BookingNotifier $notifier): RedirectResponse
    {
        try {
            $booking = $this->bookings->create([
                ...$request->safe()->except('website'),
                'children' => $request->integer('children'),
                'locale' => app()->getLocale(),
                'ip_address' => $request->ip(),
            ], BookingSource::Website);
        } catch (RoomUnavailableException $e) {
            return back()->withInput()->withErrors(['room_type_id' => $e->getMessage()]);
        }

        $notifier->requestReceived($booking);

        return redirect()->to($booking->publicUrl());
    }

    public function show(Request $request, string $reference): View
    {
        $booking = Booking::with(['roomType', 'hotel'])->where('reference', $reference)->firstOrFail();
        abort_unless(hash_equals($booking->accessToken(), (string) $request->query('t')), 404);

        return view('booking.show', ['booking' => $booking]);
    }
}
