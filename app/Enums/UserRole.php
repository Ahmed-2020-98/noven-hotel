<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum UserRole: string implements HasLabel
{
    case Admin = 'admin';
    case Reception = 'reception';
    case Editor = 'editor';

    public function getLabel(): string
    {
        return __('admin.roles.'.$this->value);
    }
}
