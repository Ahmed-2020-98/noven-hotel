<?php

namespace Database\Factories;

use App\Enums\BookingSource;
use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\RoomType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Booking> */
class BookingFactory extends Factory
{
    public function definition(): array
    {
        $checkIn = now()->addDays(fake()->numberBetween(1, 20))->startOfDay();

        return [
            'reference' => 'NV'.Str::upper(Str::random(8)),
            'room_type_id' => RoomType::factory(),
            'hotel_id' => fn (array $attributes) => RoomType::find($attributes['room_type_id'])->hotel_id,
            'check_in' => $checkIn,
            'check_out' => $checkIn->copy()->addDays(2),
            'nights' => 2,
            'rooms' => 1,
            'adults' => 2,
            'children' => 0,
            'guest_name' => fake()->name(),
            'guest_phone' => '05'.fake()->numerify('########'),
            'guest_email' => fake()->safeEmail(),
            'locale' => 'ar',
            'subtotal' => 800,
            'vat_rate' => 15,
            'vat_amount' => 120,
            'total' => 920,
            'status' => BookingStatus::Confirmed,
            'source' => BookingSource::Website,
        ];
    }

    public function stay(string $checkIn, string $checkOut, int $rooms = 1): static
    {
        return $this->state(fn () => ['check_in' => $checkIn, 'check_out' => $checkOut, 'rooms' => $rooms]);
    }

    public function status(BookingStatus $status): static
    {
        return $this->state(fn () => ['status' => $status]);
    }

    public function pending(?\DateTimeInterface $expiresAt = null): static
    {
        return $this->state(fn () => ['status' => BookingStatus::Pending, 'expires_at' => $expiresAt ?? now()->addDay()]);
    }
}
