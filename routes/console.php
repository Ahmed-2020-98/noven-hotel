<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('bookings:expire')->everyFifteenMinutes()->withoutOverlapping();
