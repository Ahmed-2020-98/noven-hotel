<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Local/demo dashboard account. Change the password after the first login in production.
        User::updateOrCreate(
            ['email' => 'admin@noven.test'],
            ['name' => 'NOVEN Admin', 'password' => env('SEED_ADMIN_PASSWORD', 'password'), 'role' => UserRole::Admin, 'is_active' => true],
        );

        $this->call(HotelContentSeeder::class);
    }
}
