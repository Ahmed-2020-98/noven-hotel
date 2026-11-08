<?php

namespace App\Exceptions;

use RuntimeException;

class RoomUnavailableException extends RuntimeException
{
    public function __construct(public readonly int $availableUnits = 0)
    {
        parent::__construct(__('booking.errors.unavailable'));
    }
}
