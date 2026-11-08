<?php

namespace App\Services;

use App\Enums\BookingSource;
use App\Enums\BookingStatus;
use App\Exceptions\RoomUnavailableException;
use App\Models\Booking;
use App\Models\RoomType;
use App\Models\Setting;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BookingService
{
    /**
     * Price breakdown for a stay. Nights are [checkIn, checkOut).
     *
     * @return array{nights: int, rooms: int, nightly: list<array{date: string, price: float, weekend: bool}>, subtotal: float, vat_rate: float, vat_amount: float, total: float}
     */
    public function quote(RoomType $roomType, CarbonInterface $checkIn, CarbonInterface $checkOut, int $rooms = 1): array
    {
        $checkIn = CarbonImmutable::parse($checkIn)->startOfDay();
        $checkOut = CarbonImmutable::parse($checkOut)->startOfDay();

        $seasons = $roomType->seasons()
            ->where('starts_on', '<', $checkOut)
            ->where('ends_on', '>=', $checkIn)
            ->orderByDesc('starts_on')
            ->get();

        $weekendDays = array_map('intval', (array) Setting::read('weekend_days'));
        $nightly = [];

        foreach ($this->nights($checkIn, $checkOut) as $night) {
            $isWeekend = in_array($night->dayOfWeek, $weekendDays, true);
            $season = $seasons->first(fn ($s) => $night->betweenIncluded($s->starts_on, $s->ends_on));

            $price = match (true) {
                $season !== null && $isWeekend && $season->weekend_price !== null => $season->weekend_price,
                $season !== null => $season->price,
                $isWeekend && $roomType->weekend_price !== null => $roomType->weekend_price,
                default => $roomType->base_price,
            };

            $nightly[] = ['date' => $night->toDateString(), 'price' => (float) $price, 'weekend' => $isWeekend];
        }

        $vatRate = (float) Setting::read('vat_rate');
        $subtotal = round(array_sum(array_column($nightly, 'price')) * $rooms, 2);
        $vatAmount = round($subtotal * $vatRate / 100, 2);

        return [
            'nights' => count($nightly),
            'rooms' => $rooms,
            'nightly' => $nightly,
            'subtotal' => $subtotal,
            'vat_rate' => $vatRate,
            'vat_amount' => $vatAmount,
            'total' => round($subtotal + $vatAmount, 2),
        ];
    }

    /** Lowest number of free units across every night of the stay. */
    public function availableUnits(RoomType $roomType, CarbonInterface $checkIn, CarbonInterface $checkOut, ?int $ignoreBookingId = null): int
    {
        $perNight = $this->availabilityByNight($roomType, CarbonImmutable::parse($checkIn), CarbonImmutable::parse($checkOut), $ignoreBookingId);

        return $perNight === [] ? 0 : max(0, min($perNight));
    }

    /**
     * Free units for each night in [from, to).
     *
     * @return array<string, int>
     */
    public function availabilityByNight(RoomType $roomType, CarbonImmutable $from, CarbonImmutable $to, ?int $ignoreBookingId = null): array
    {
        $bookings = Booking::query()
            ->where('room_type_id', $roomType->id)
            ->holding()
            ->overlapping($from->toDateString(), $to->toDateString())
            ->when($ignoreBookingId, fn ($q) => $q->whereKeyNot($ignoreBookingId))
            ->get(['check_in', 'check_out', 'rooms']);

        $closures = $roomType->closures()
            ->where('starts_on', '<', $to)
            ->where('ends_on', '>=', $from)
            ->get(['starts_on', 'ends_on', 'units']);

        $result = [];
        foreach ($this->nights($from, $to) as $night) {
            $booked = $bookings
                ->filter(fn ($b) => $night->gte($b->check_in) && $night->lt($b->check_out))
                ->sum('rooms');
            $closed = $closures
                ->filter(fn ($c) => $night->betweenIncluded($c->starts_on, $c->ends_on))
                ->sum('units');

            $result[$night->toDateString()] = $roomType->units_count - $booked - $closed;
        }

        return $result;
    }

    /**
     * Free units per room type per night, for the occupancy calendar.
     *
     * @param  Collection<int, RoomType>  $roomTypes
     * @return array<int, array<string, int>>
     */
    public function availabilityMatrix(Collection $roomTypes, CarbonImmutable $from, int $days): array
    {
        $to = $from->addDays($days);

        return $roomTypes->mapWithKeys(
            fn (RoomType $roomType) => [$roomType->id => $this->availabilityByNight($roomType, $from, $to)]
        )->all();
    }

    /**
     * Create a booking after re-checking availability under a row lock,
     * so two guests cannot take the last unit at the same time.
     *
     * @param  array{room_type_id: int, check_in: string, check_out: string, rooms?: int, adults: int, children?: int, guest_name: string, guest_phone: string, guest_email?: ?string, guest_nationality?: ?string, notes?: ?string, locale?: string, ip_address?: ?string}  $data
     *
     * @throws RoomUnavailableException
     */
    public function create(array $data, BookingSource $source = BookingSource::Website, BookingStatus $status = BookingStatus::Pending): Booking
    {
        return DB::transaction(function () use ($data, $source, $status) {
            /** @var RoomType $roomType */
            $roomType = RoomType::whereKey($data['room_type_id'])->lockForUpdate()->firstOrFail();

            $checkIn = CarbonImmutable::parse($data['check_in']);
            $checkOut = CarbonImmutable::parse($data['check_out']);
            $rooms = (int) ($data['rooms'] ?? 1);

            $available = $this->availableUnits($roomType, $checkIn, $checkOut);
            if ($available < $rooms) {
                throw new RoomUnavailableException($available);
            }

            $quote = $this->quote($roomType, $checkIn, $checkOut, $rooms);

            $booking = Booking::create([
                ...collect($data)->only([
                    'room_type_id', 'adults', 'children', 'guest_name', 'guest_phone',
                    'guest_email', 'guest_nationality', 'notes', 'locale', 'ip_address',
                ])->all(),
                'reference' => $this->newReference(),
                'hotel_id' => $roomType->hotel_id,
                'check_in' => $checkIn,
                'check_out' => $checkOut,
                'rooms' => $rooms,
                'nights' => $quote['nights'],
                'nightly_rates' => $quote['nightly'],
                'subtotal' => $quote['subtotal'],
                'vat_rate' => $quote['vat_rate'],
                'vat_amount' => $quote['vat_amount'],
                'total' => $quote['total'],
                'status' => $status,
                'source' => $source,
                'expires_at' => $status === BookingStatus::Pending
                    ? now()->addHours((int) Setting::read('pending_hold_hours'))
                    : null,
                'confirmed_at' => $status === BookingStatus::Confirmed ? now() : null,
            ]);

            $booking->statusLogs()->create(['to_status' => $status->value, 'user_id' => auth()->id()]);

            return $booking;
        });
    }

    /**
     * Update a booking from the dashboard. When the room, dates or room count change,
     * availability is re-checked (ignoring this booking) and the price is recalculated.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws RoomUnavailableException
     */
    public function update(Booking $booking, array $data): Booking
    {
        return DB::transaction(function () use ($booking, $data) {
            $booking->fill($data);

            if ($booking->isDirty(['room_type_id', 'check_in', 'check_out', 'rooms'])) {
                /** @var RoomType $roomType */
                $roomType = RoomType::whereKey($booking->room_type_id)->lockForUpdate()->firstOrFail();

                $available = $this->availableUnits($roomType, $booking->check_in, $booking->check_out, $booking->id);
                if ($available < $booking->rooms) {
                    throw new RoomUnavailableException($available);
                }

                $quote = $this->quote($roomType, $booking->check_in, $booking->check_out, $booking->rooms);
                $booking->fill([
                    'hotel_id' => $roomType->hotel_id,
                    'nights' => $quote['nights'],
                    'nightly_rates' => $quote['nightly'],
                    'subtotal' => $quote['subtotal'],
                    'vat_rate' => $quote['vat_rate'],
                    'vat_amount' => $quote['vat_amount'],
                    'total' => $quote['total'],
                ]);
            }

            $booking->save();

            return $booking;
        });
    }

    /** Release inventory held by pending requests nobody confirmed in time. */
    public function expireStalePending(): int
    {
        $count = 0;
        Booking::where('status', BookingStatus::Pending)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->each(function (Booking $booking) use (&$count) {
                $booking->transitionTo(BookingStatus::Expired, 'auto-expired');
                $count++;
            });

        return $count;
    }

    /** @return list<CarbonImmutable> */
    private function nights(CarbonImmutable $from, CarbonImmutable $to): array
    {
        $nights = [];
        for ($night = $from->startOfDay(); $night->lt($to); $night = $night->addDay()) {
            $nights[] = $night;
        }

        return $nights;
    }

    private function newReference(): string
    {
        do {
            $reference = 'NV'.now()->format('ym').'-'.Str::upper(Str::random(4));
        } while (Booking::where('reference', $reference)->exists());

        return $reference;
    }
}
