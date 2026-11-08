<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Sent to the guest when a request is received, confirmed or cancelled.
 */
class GuestBookingMail extends Mailable
{
    use Queueable, SerializesModels;

    /** @param 'received'|'confirmed'|'cancelled' $kind */
    public function __construct(public Booking $booking, public string $kind) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __("mail.guest.{$this->kind}.subject", ['ref' => $this->booking->reference]),
        );
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.booking.guest');
    }
}
