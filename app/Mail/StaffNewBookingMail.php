<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Alerts the front desk that a new booking request arrived from the website.
 */
class StaffNewBookingMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Booking $booking) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: "طلب حجز جديد {$this->booking->reference} — {$this->booking->guest_name}");
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.booking.staff');
    }
}
