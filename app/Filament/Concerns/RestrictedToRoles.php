<?php

namespace App\Filament\Concerns;

use App\Enums\UserRole;
use App\Models\User;

/**
 * Limits a Filament resource or page to the roles returned by allowedRoles().
 */
trait RestrictedToRoles
{
    /** @return list<UserRole> */
    abstract public static function allowedRoles(): array;

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user instanceof User && $user->hasRole(...static::allowedRoles());
    }
}
