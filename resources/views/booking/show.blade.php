@php
    $b = $booking;
    $waText = __('site.booking.whatsapp_message', ['ref' => $b->reference, 'room' => $b->roomType->tr('name'), 'in' => $b->check_in->format('Y-m-d'), 'out' => $b->check_out->format('Y-m-d')]);
@endphp
<x-layout :title="__('site.booking.reference').' '.$b->reference">
    <x-slot:head><meta name="robots" content="noindex"></x-slot:head>
    <section class="container-x max-w-3xl py-16">
        <div class="text-center">
            <span class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-green-mist text-green">{{ svg('heroicon-o-check-circle', 'h-9 w-9') }}</span>
            <h1 class="mt-5 text-3xl text-green sm:text-4xl">{{ __('site.booking.thanks') }}</h1>
            <p class="mt-3 text-muted">{{ __('site.booking.thanks_text', ['ref' => $b->reference]) }}</p>
        </div>

        <div class="mt-10 overflow-hidden rounded-2xl border border-green-line">
            <dl class="divide-y divide-green-line text-sm">
                @foreach ([
                    __('site.booking.reference') => $b->reference,
                    __('site.booking.status') => $b->status->getLabel(),
                    __('site.booking.room_type') => $b->roomType->tr('name').($b->rooms > 1 ? ' '.__('site.booking.rooms_x', ['count' => $b->rooms]) : ''),
                    __('site.common.check_in') => $b->check_in->format('Y-m-d'),
                    __('site.common.check_out') => $b->check_out->format('Y-m-d'),
                    __('site.common.adults').' / '.__('site.common.children') => $b->adults.' / '.$b->children,
                    __('site.booking.name') => $b->guest_name,
                ] as $label => $value)
                    <div class="flex justify-between gap-4 px-5 py-3.5">
                        <dt class="text-muted">{{ $label }}</dt>
                        <dd class="text-end font-medium">{{ $value }}</dd>
                    </div>
                @endforeach
                <div class="flex justify-between gap-4 bg-green-mist px-5 py-3.5">
                    <dt class="text-muted">{{ trans_choice('site.booking.nights', $b->nights, ['count' => $b->nights]) }}</dt>
                    <dd>{{ number_format((float) $b->subtotal, 2) }} {{ __('site.common.sar') }}</dd>
                </div>
                <div class="flex justify-between gap-4 bg-green-mist px-5 py-3.5">
                    <dt class="text-muted">{{ __('site.booking.vat', ['rate' => rtrim(rtrim((string) $b->vat_rate, '0'), '.')]) }}</dt>
                    <dd>{{ number_format((float) $b->vat_amount, 2) }} {{ __('site.common.sar') }}</dd>
                </div>
                <div class="flex justify-between gap-4 bg-green px-5 py-4 text-white">
                    <dt class="font-semibold">{{ __('site.booking.total') }}</dt>
                    <dd class="text-xl font-semibold text-gold">{{ number_format((float) $b->total, 2) }} {{ __('site.common.sar') }}</dd>
                </div>
            </dl>
        </div>

        @if ($b->status === \App\Enums\BookingStatus::Pending)
            <p class="mt-4 text-center text-sm text-muted">{{ __('site.booking.hold_note', ['hours' => \App\Models\Setting::read('pending_hold_hours')]) }}</p>
        @endif

        <div class="mt-8 flex flex-wrap justify-center gap-3">
            @if ($b->hotel?->whatsappNumber())
                <a href="https://wa.me/{{ $b->hotel->whatsappNumber() }}?text={{ rawurlencode($waText) }}" target="_blank" rel="noopener" class="btn bg-[#25D366] text-white hover:brightness-95">{{ __('site.booking.whatsapp_hotel') }}</a>
            @endif
            @if ($b->hotel?->phone)
                <a href="tel:{{ preg_replace('/\s+/', '', $b->hotel->phone) }}" class="btn-outline">{{ __('site.common.call') }}</a>
            @endif
            <a href="{{ route('home') }}" class="btn-outline">{{ __('site.nav.home') }}</a>
        </div>
    </section>
</x-layout>
