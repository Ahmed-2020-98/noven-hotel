<?php

namespace Database\Factories;

use App\Models\Hotel;
use App\Models\RoomType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<RoomType> */
class RoomTypeFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'hotel_id' => Hotel::factory(),
            'slug' => Str::slug($name),
            'name_ar' => 'غرفة '.$name,
            'name_en' => Str::title($name).' Room',
            'size_sqm' => 28,
            'max_adults' => 2,
            'max_children' => 1,
            'base_price' => 400,
            'weekend_price' => null,
            'units_count' => 3,
            'is_active' => true,
        ];
    }
}
