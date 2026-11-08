<?php

namespace App\Models;

use App\Enums\BookingSource;
use App\Enums\BookingStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Booking extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'check_in' => 'date',
            'check_out' => 'date',
            'nightly_rates' => 'array',
            'subtotal' => 'decimal:2',
            'vat_rate' => 'decimal:2',
            'vat_amount' => 'decimal:2',
            'total' => 'decimal:2',
            'status' => BookingStatus::class,
            'source' => BookingSource::class,
            'expires_at' => 'datetime',
            'confirmed_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }

    public function roomType(): BelongsTo
    {
        return $this->belongsTo(RoomType::class);
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(BookingStatusLog::class)->latest();
    }

    /** Bookings that currently occupy inventory. */
    public function scopeHolding(Builder $query): Builder
    {
        return $query->where(function (Builder $q) {
            $q->whereIn('status', [BookingStatus::Confirmed->value, BookingStatus::CheckedIn->value])
                ->orWhere(function (Builder $q) {
                    $q->where('status', BookingStatus::Pending->value)
                        ->where(fn (Builder $q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()));
                });
        });
    }

    /** Overlaps the stay [from, to) — check_out day is not a night. */
    public function scopeOverlapping(Builder $query, $from, $to): Builder
    {
        return $query->where('check_in', '<', $to)->where('check_out', '>', $from);
    }

    public function transitionTo(BookingStatus $status, ?string $note = null, ?int $userId = null): void
    {
        $from = $this->status;

        $this->status = $status;
        match ($status) {
            BookingStatus::Confirmed => $this->forceFill(['confirmed_at' => now(), 'expires_at' => null]),
            BookingStatus::Cancelled => $this->cancelled_at = now(),
            default => null,
        };
        $this->save();

        $this->statusLogs()->create([
            'from_status' => $from?->value,
            'to_status' => $status->value,
            'user_id' => $userId ?? auth()->id(),
            'note' => $note,
        ]);
    }

    /** Unguessable token that lets the guest open their request page. */
    public function accessToken(): string
    {
        return substr(hash_hmac('sha256', $this->reference, (string) config('app.key')), 0, 24);
    }

    public function publicUrl(): string
    {
        return route('booking.show', ['locale' => $this->locale, 'reference' => $this->reference, 't' => $this->accessToken()]);
    }

    public function guestWhatsappNumber(): string
    {
        $digits = preg_replace('/\D+/', '', $this->guest_phone);
        // Local Saudi mobile (05xxxxxxxx) → international 9665xxxxxxxx
        if (str_starts_with($digits, '05') && strlen($digits) === 10) {
            $digits = '966'.substr($digits, 1);
        }

        return ltrim($digits, '0');
    }
}
