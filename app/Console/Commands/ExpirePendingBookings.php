<?php

namespace App\Console\Commands;

use App\Services\BookingService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('bookings:expire')]
#[Description('Expire pending booking requests whose hold time has passed')]
class ExpirePendingBookings extends Command
{
    public function handle(BookingService $bookings): int
    {
        $count = $bookings->expireStalePending();
        $this->info("Expired {$count} pending request(s).");

        return self::SUCCESS;
    }
}
