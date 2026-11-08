<?php

namespace Database\Factories;

use App\Models\Hotel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Hotel> */
class HotelFactory extends Factory
{
    public function definition(): array
    {
        return [
            'slug' => Str::slug(fake()->unique()->words(2, true)),
            'name_ar' => 'فندق نوفين',
            'name_en' => 'NOVEN Hotel',
            'city_ar' => 'الرياض',
            'city_en' => 'Riyadh',
            'phone' => '+966500000000',
            'whatsapp' => '+966500000000',
            'email' => fake()->safeEmail(),
            'is_active' => true,
        ];
    }
}
