<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum BookingStatus: string implements HasColor, HasLabel
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case CheckedIn = 'checked_in';
    case CheckedOut = 'checked_out';
    case Cancelled = 'cancelled';
    case Expired = 'expired';

    public function getLabel(): string
    {
        return __('booking.status.'.$this->value);
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Confirmed => 'success',
            self::CheckedIn => 'info',
            self::CheckedOut => 'gray',
            self::Cancelled, self::Expired => 'danger',
        };
    }

    /** Statuses that occupy inventory (pending only while not expired). */
    public static function holding(): array
    {
        return [self::Pending->value, self::Confirmed->value, self::CheckedIn->value];
    }

    /** Allowed transitions for the dashboard workflow. */
    public function next(): array
    {
        return match ($this) {
            self::Pending => [self::Confirmed, self::Cancelled],
            self::Confirmed => [self::CheckedIn, self::Cancelled],
            self::CheckedIn => [self::CheckedOut],
            default => [],
        };
    }
}
