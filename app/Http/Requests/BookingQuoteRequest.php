<?php

namespace App\Http\Requests;

use App\Models\RoomType;
use App\Models\Setting;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Stay rules shared by the live price quote and the booking request.
 */
class BookingQuoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'room_type_id' => ['required', 'integer', Rule::exists('room_types', 'id')->where('is_active', true)],
            'check_in' => ['required', 'date', 'after_or_equal:today'],
            'check_out' => ['required', 'date', 'after:check_in'],
            'rooms' => ['required', 'integer', 'min:1', 'max:'.(int) Setting::read('max_rooms_per_booking')],
            'adults' => ['required', 'integer', 'min:1', 'max:30'],
            'children' => ['nullable', 'integer', 'min:0', 'max:20'],
        ];
    }

    /** @return array<int, callable> */
    public function after(): array
    {
        return [function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $nights = CarbonImmutable::parse($this->input('check_in'))->diffInDays(CarbonImmutable::parse($this->input('check_out')));
            $maxNights = (int) Setting::read('max_nights');
            if ($nights > $maxNights) {
                $validator->errors()->add('check_out', __('validation.max.numeric', ['attribute' => __('validation.attributes.check_out'), 'max' => $maxNights]));
            }

            $room = RoomType::find($this->input('room_type_id'));
            $rooms = (int) $this->input('rooms');
            if ($room && ($this->integer('adults') > $room->max_adults * $rooms || $this->integer('children') > $room->max_children * $rooms)) {
                $validator->errors()->add('adults', __('booking.errors.max_guests'));
            }
        }];
    }

    public function roomType(): RoomType
    {
        return RoomType::findOrFail($this->input('room_type_id'));
    }
}
