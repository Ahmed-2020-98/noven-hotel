<?php

namespace App\Http\Requests;

class StoreBookingRequest extends BookingQuoteRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            ...parent::rules(),
            'guest_name' => ['required', 'string', 'min:3', 'max:120'],
            'guest_phone' => ['required', 'string', 'regex:/^[+0-9\s\-()]{8,20}$/'],
            'guest_email' => ['nullable', 'email', 'max:150'],
            'guest_nationality' => ['nullable', 'string', 'max:60'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'website' => ['prohibited'], // honeypot
        ];
    }
}
