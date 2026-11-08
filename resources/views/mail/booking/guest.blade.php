@php($b = $booking)
<x-mail::message>
# {{ __("mail.guest.{$kind}.title") }}

{{ __('mail.hello', ['name' => $b->guest_name]) }}

{{ __("mail.guest.{$kind}.intro") }}

<x-mail::table>
| | |
|:--|:--|
| {{ __('site.booking.reference') }} | **{{ $b->reference }}** |
| {{ __('site.booking.room_type') }} | {{ $b->roomType->tr('name') }} {{ $b->rooms > 1 ? __('site.booking.rooms_x', ['count' => $b->rooms]) : '' }} |
| {{ __('site.common.check_in') }} | {{ $b->check_in->format('Y-m-d') }} |
| {{ __('site.common.check_out') }} | {{ $b->check_out->format('Y-m-d') }} |
| {{ __('site.common.adults') }} / {{ __('site.common.children') }} | {{ $b->adults }} / {{ $b->children }} |
| {{ __('site.booking.total') }} | **{{ number_format((float) $b->total, 2) }} {{ __('site.common.sar') }}** |
</x-mail::table>

<x-mail::button :url="$b->publicUrl()" color="success">
{{ __('mail.view') }}
</x-mail::button>

@if ($b->hotel?->phone)
{{ __('mail.questions', ['phone' => $b->hotel->phone]) }}
@endif

{{ __('mail.regards') }}
</x-mail::message>
