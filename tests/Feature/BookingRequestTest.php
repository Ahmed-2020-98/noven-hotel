<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Mail\GuestBookingMail;
use App\Mail\StaffNewBookingMail;
use App\Models\Booking;
use App\Models\Hotel;
use App\Models\RoomType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class BookingRequestTest extends TestCase
{
    use RefreshDatabase;

    private RoomType $room;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->travelTo('2027-01-01 10:00');
        $hotel = Hotel::factory()->create(['email' => 'desk@noven.test']);
        $this->room = RoomType::factory()->for($hotel)->create(['base_price' => 400, 'units_count' => 1, 'max_adults' => 2, 'max_children' => 1]);
    }

    /** @return array<string, mixed> */
    private function payload(array $overrides = []): array
    {
        return [
            'room_type_id' => $this->room->id,
            'check_in' => '2027-01-03',
            'check_out' => '2027-01-05',
            'rooms' => 1,
            'adults' => 2,
            'children' => 0,
            'guest_name' => 'Sara Ahmed',
            'guest_phone' => '0501234567',
            'guest_email' => 'sara@example.com',
            ...$overrides,
        ];
    }

    public function test_quote_returns_price_and_availability(): void
    {
        $this->postJson('/ar/booking/quote', $this->payload())
            ->assertOk()
            ->assertJson(['available' => true, 'units_left' => 1, 'nights' => 2, 'subtotal' => 800, 'vat_amount' => 120, 'total' => 920]);
    }

    public function test_quote_rejects_past_dates_and_too_many_guests(): void
    {
        $this->postJson('/en/booking/quote', $this->payload(['check_in' => '2026-12-30']))->assertJsonValidationErrors('check_in');
        $this->postJson('/en/booking/quote', $this->payload(['adults' => 3]))->assertJsonValidationErrors('adults');
    }

    public function test_guest_can_send_request_and_see_confirmation_page(): void
    {
        Mail::fake();

        $response = $this->post('/en/booking', $this->payload());

        $booking = Booking::sole();
        $response->assertRedirect($booking->publicUrl());
        $this->assertSame(BookingStatus::Pending, $booking->status);
        $this->assertSame('en', $booking->locale);
        $this->assertSame('920.00', $booking->total);

        Mail::assertSent(GuestBookingMail::class, fn ($mail) => $mail->hasTo('sara@example.com') && $mail->kind === 'received');
        Mail::assertSent(StaffNewBookingMail::class, fn ($mail) => $mail->hasTo('desk@noven.test'));

        $this->get($booking->publicUrl())->assertOk()->assertSee($booking->reference)->assertSee('920.00');
    }

    public function test_confirmation_page_needs_the_token(): void
    {
        $booking = Booking::factory()->for($this->room)->create(['locale' => 'ar']);

        $this->get("/ar/booking/{$booking->reference}")->assertNotFound();
        $this->get("/ar/booking/{$booking->reference}?t=wrong")->assertNotFound();
    }

    public function test_request_for_full_room_returns_error(): void
    {
        Booking::factory()->for($this->room)->stay('2027-01-04', '2027-01-06')->create();

        $this->from('/ar/booking')->post('/ar/booking', $this->payload())
            ->assertRedirect('/ar/booking')
            ->assertSessionHasErrors('room_type_id');
        $this->assertSame(1, Booking::count());
    }

    public function test_honeypot_blocks_bots(): void
    {
        $this->post('/ar/booking', $this->payload(['website' => 'http://spam']))->assertSessionHasErrors('website');
        $this->assertSame(0, Booking::count());
    }
}
