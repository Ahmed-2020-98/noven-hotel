<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Exceptions\RoomUnavailableException;
use App\Models\Booking;
use App\Models\RateSeason;
use App\Models\RoomClosure;
use App\Models\RoomType;
use App\Models\Setting;
use App\Services\BookingService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingServiceTest extends TestCase
{
    use RefreshDatabase;

    private BookingService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(BookingService::class);
        // Sunday 2027-01-03 — weekend nights default to Thursday (4) & Friday (5).
        $this->travelTo(CarbonImmutable::parse('2027-01-01 10:00'));
    }

    private function day(string $date): CarbonImmutable
    {
        return CarbonImmutable::parse($date);
    }

    public function test_weekday_stay_uses_base_price_plus_vat(): void
    {
        $room = RoomType::factory()->create(['base_price' => 400, 'weekend_price' => 500]);

        // Sun 3 → Tue 5: Sunday + Monday nights
        $quote = $this->service->quote($room, $this->day('2027-01-03'), $this->day('2027-01-05'));

        $this->assertSame(2, $quote['nights']);
        $this->assertSame(800.0, $quote['subtotal']);
        $this->assertSame(120.0, $quote['vat_amount']);
        $this->assertSame(920.0, $quote['total']);
    }

    public function test_thursday_and_friday_nights_use_weekend_price(): void
    {
        $room = RoomType::factory()->create(['base_price' => 400, 'weekend_price' => 500]);

        // Wed 6 → Sat 9: Wed (400) + Thu (500) + Fri (500)
        $quote = $this->service->quote($room, $this->day('2027-01-06'), $this->day('2027-01-09'));

        $this->assertSame(1400.0, $quote['subtotal']);
        $this->assertSame([false, true, true], array_column($quote['nightly'], 'weekend'));
    }

    public function test_season_price_overrides_base_price_and_multiplies_by_rooms(): void
    {
        $room = RoomType::factory()->create(['base_price' => 400]);
        RateSeason::create(['room_type_id' => $room->id, 'name' => 'Peak', 'starts_on' => '2027-01-04', 'ends_on' => '2027-01-04', 'price' => 700]);

        // Sun 3 (400) + Mon 4 (season 700), two rooms
        $quote = $this->service->quote($room, $this->day('2027-01-03'), $this->day('2027-01-05'), 2);

        $this->assertSame(2200.0, $quote['subtotal']);
    }

    public function test_vat_rate_comes_from_settings(): void
    {
        Setting::put(['vat_rate' => 0]);
        $room = RoomType::factory()->create(['base_price' => 400]);

        $quote = $this->service->quote($room, $this->day('2027-01-03'), $this->day('2027-01-04'));

        $this->assertSame(400.0, $quote['total']);
    }

    public function test_overlapping_bookings_and_closures_reduce_availability(): void
    {
        $room = RoomType::factory()->create(['units_count' => 3]);
        Booking::factory()->for($room)->stay('2027-01-03', '2027-01-06')->create();
        RoomClosure::create(['room_type_id' => $room->id, 'starts_on' => '2027-01-05', 'ends_on' => '2027-01-05', 'units' => 1]);

        $this->assertSame(1, $this->service->availableUnits($room, $this->day('2027-01-04'), $this->day('2027-01-07')));
        // Check-out day is not a night: a stay starting on 6th is unaffected by the booking.
        $this->assertSame(3, $this->service->availableUnits($room, $this->day('2027-01-06'), $this->day('2027-01-08')));
    }

    public function test_cancelled_and_expired_requests_do_not_hold_inventory(): void
    {
        $room = RoomType::factory()->create(['units_count' => 2]);
        Booking::factory()->for($room)->stay('2027-01-03', '2027-01-05')->status(BookingStatus::Cancelled)->create();
        Booking::factory()->for($room)->stay('2027-01-03', '2027-01-05')->pending(now()->subMinute())->create();
        Booking::factory()->for($room)->stay('2027-01-03', '2027-01-05')->pending()->create();

        $this->assertSame(1, $this->service->availableUnits($room, $this->day('2027-01-03'), $this->day('2027-01-05')));
    }

    public function test_create_rejects_request_when_room_type_is_full(): void
    {
        $room = RoomType::factory()->create(['units_count' => 1]);
        Booking::factory()->for($room)->stay('2027-01-03', '2027-01-05')->create();

        $this->expectException(RoomUnavailableException::class);

        $this->service->create($this->payload($room, ['check_in' => '2027-01-04', 'check_out' => '2027-01-06']));
    }

    public function test_create_stores_pending_booking_with_price_and_hold(): void
    {
        $room = RoomType::factory()->create(['base_price' => 400, 'units_count' => 2]);

        $booking = $this->service->create($this->payload($room));

        $this->assertSame(BookingStatus::Pending, $booking->status);
        $this->assertSame('920.00', $booking->total);
        $this->assertSame(2, $booking->nights);
        $this->assertTrue($booking->expires_at->equalTo(now()->addHours(24)));
        $this->assertMatchesRegularExpression('/^NV\d{4}-[A-Z0-9]{4}$/', $booking->reference);
        $this->assertDatabaseHas('booking_status_logs', ['booking_id' => $booking->id, 'to_status' => 'pending']);
    }

    public function test_stale_pending_requests_expire(): void
    {
        $room = RoomType::factory()->create();
        $stale = Booking::factory()->for($room)->pending(now()->subHour())->create();
        $fresh = Booking::factory()->for($room)->pending(now()->addHour())->create();

        $this->assertSame(1, $this->service->expireStalePending());
        $this->assertSame(BookingStatus::Expired, $stale->fresh()->status);
        $this->assertSame(BookingStatus::Pending, $fresh->fresh()->status);
    }

    /** @return array<string, mixed> */
    private function payload(RoomType $room, array $overrides = []): array
    {
        return [
            'room_type_id' => $room->id,
            'check_in' => '2027-01-03',
            'check_out' => '2027-01-05',
            'rooms' => 1,
            'adults' => 2,
            'guest_name' => 'Test Guest',
            'guest_phone' => '0500000000',
            ...$overrides,
        ];
    }
}
