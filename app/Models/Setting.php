<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['value' => 'json'];
    }

    /** Defaults used when a key has never been saved from the dashboard. */
    public const DEFAULTS = [
        'vat_rate' => 15,
        'weekend_days' => [4, 5], // Thursday & Friday nights (Carbon dayOfWeek)
        'pending_hold_hours' => 24,
        'max_rooms_per_booking' => 5,
        'max_nights' => 30,
        'notification_emails' => [],
    ];

    public static function cached(): array
    {
        return Cache::rememberForever('settings', fn () => static::pluck('value', 'key')->all());
    }

    public static function read(string $key, mixed $default = null): mixed
    {
        $value = static::cached()[$key] ?? null;

        return ($value === null || $value === '') ? ($default ?? static::DEFAULTS[$key] ?? null) : $value;
    }

    /** Localized text setting: {key}_{locale} with fallback. */
    public static function text(string $key, ?string $locale = null): ?string
    {
        $locale ??= app()->getLocale();

        return static::read("{$key}_{$locale}") ?: static::read($key.'_'.($locale === 'ar' ? 'en' : 'ar'));
    }

    public static function put(array $values): void
    {
        foreach ($values as $key => $value) {
            static::updateOrCreate(['key' => $key], ['value' => $value]);
        }
        Cache::forget('settings');
    }
}
