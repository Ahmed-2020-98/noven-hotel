<?php

namespace App\Filament\Support;

/**
 * Heroicon names offered in the dashboard for amenities and services.
 */
class IconOptions
{
    /** @return array<string, string> */
    public static function all(): array
    {
        return [
            'wifi' => 'واي فاي',
            'sun' => 'تكييف / شمس',
            'tv' => 'تلفزيون',
            'cube' => 'ميني بار',
            'sparkles' => 'نظافة / فخامة',
            'lock-closed' => 'خزنة',
            'beaker' => 'قهوة وشاي',
            'briefcase' => 'عمل',
            'cake' => 'إفطار / مطعم',
            'clock' => 'على مدار الساعة',
            'truck' => 'مواقف / نقل',
            'user-group' => 'اجتماعات',
            'key' => 'مفتاح',
            'shield-check' => 'أمان',
            'fire' => 'تدفئة',
            'moon' => 'هدوء',
            'gift' => 'هدايا / عروض',
            'star' => 'نجمة',
            'heart' => 'قلب',
            'map-pin' => 'موقع',
            'phone' => 'هاتف',
            'computer-desktop' => 'كمبيوتر',
            'building-office-2' => 'مبنى',
            'check-circle' => 'علامة صح',
        ];
    }
}
