<?php

namespace App\Models\Concerns;

/**
 * Bilingual columns stored as {field}_ar / {field}_en.
 * $model->tr('name') returns the value for the current locale with fallback.
 */
trait Localized
{
    public function tr(string $field, ?string $locale = null): ?string
    {
        $locale ??= app()->getLocale();
        $fallback = $locale === 'ar' ? 'en' : 'ar';

        return $this->getAttribute("{$field}_{$locale}") ?: $this->getAttribute("{$field}_{$fallback}");
    }
}
