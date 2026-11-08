<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum BookingSource: string implements HasLabel
{
    case Website = 'website';
    case Phone = 'phone';
    case WhatsApp = 'whatsapp';
    case WalkIn = 'walk_in';

    public function getLabel(): string
    {
        return __('booking.source.'.$this->value);
    }
}
