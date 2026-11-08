<?php

namespace App\Models;

use App\Models\Concerns\Localized;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class RoomType extends Model
{
    use HasFactory, Localized;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'base_price' => 'decimal:2',
            'weekend_price' => 'decimal:2',
            'gallery' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }

    public function amenities(): BelongsToMany
    {
        return $this->belongsToMany(Amenity::class)->orderBy('sort_order');
    }

    public function seasons(): HasMany
    {
        return $this->hasMany(RateSeason::class);
    }

    public function closures(): HasMany
    {
        return $this->hasMany(RoomClosure::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order')->orderBy('id');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function coverUrl(): ?string
    {
        return $this->cover_image ? Storage::disk('public')->url($this->cover_image) : null;
    }

    /** @return list<string> */
    public function galleryUrls(): array
    {
        return collect($this->gallery ?? [])->map(fn ($p) => Storage::disk('public')->url($p))->all();
    }

    public function maxGuests(): int
    {
        return $this->max_adults + $this->max_children;
    }
}
