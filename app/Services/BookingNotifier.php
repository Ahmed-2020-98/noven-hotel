<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Mail\GuestBookingMail;
use App\Mail\StaffNewBookingMail;
use App\Models\Booking;
use App\Models\Setting;
use Illuminate\Contracts\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Sends booking emails. A mail failure is logged and never breaks the booking flow.
 */
class BookingNotifier
{
    public function requestReceived(Booking $booking): void
    {
        $this->toGuest($booking, 'received');

        $staff = $this->staffRecipients($booking);
        if ($staff !== []) {
            $this->send($staff, new StaffNewBookingMail($booking), 'ar');
        }
    }

    public function statusChanged(Booking $booking): void
    {
        match ($booking->status) {
            BookingStatus::Confirmed => $this->toGuest($booking, 'confirmed'),
            BookingStatus::Cancelled => $this->toGuest($booking, 'cancelled'),
            default => null,
        };
    }

    /** @return list<string> */
    public function staffRecipients(Booking $booking): array
    {
        $emails = array_filter((array) Setting::read('notification_emails'));
        if ($emails === [] && $booking->hotel?->email) {
            $emails = [$booking->hotel->email];
        }

        return array_values($emails);
    }

    private function toGuest(Booking $booking, string $kind): void
    {
        if ($booking->guest_email) {
            $this->send([$booking->guest_email], new GuestBookingMail($booking, $kind), $booking->locale);
        }
    }

    /** @param list<string> $to */
    private function send(array $to, Mailable $mail, string $locale): void
    {
        try {
            Mail::to($to)->locale($locale)->send($mail);
        } catch (Throwable $e) {
            Log::error('Booking mail failed', ['error' => $e->getMessage(), 'to' => $to]);
        }
    }
}
