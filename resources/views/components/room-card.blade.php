@props(['room'])
<article class="group flex flex-col overflow-hidden rounded-2xl border border-green-line bg-white transition hover:-translate-y-1 hover:shadow-xl hover:shadow-green/10">
    <a href="{{ route('rooms.show', $room) }}" class="block aspect-[4/3] overflow-hidden">
        <x-media :src="$room->coverUrl()" :alt="$room->tr('name')" class="transition duration-500 group-hover:scale-105" />
    </a>
    <div class="flex flex-1 flex-col p-5">
        <h3 class="text-2xl text-green"><a href="{{ route('rooms.show', $room) }}">{{ $room->tr('name') }}</a></h3>
        <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-sm text-muted">
            @if ($room->size_sqm)
                <span class="inline-flex items-center gap-1.5">{{ svg('heroicon-o-arrows-pointing-out', 'h-4 w-4 text-gold') }} {{ $room->size_sqm }} {{ __('site.common.sqm') }}</span>
            @endif
            <span class="inline-flex items-center gap-1.5">{{ svg('heroicon-o-users', 'h-4 w-4 text-gold') }} {{ __('site.common.guests', ['count' => $room->maxGuests()]) }}</span>
        </div>
        @if ($room->tr('summary'))
            <p class="mt-3 line-clamp-2 text-sm leading-6 text-green-deep/75">{{ $room->tr('summary') }}</p>
        @endif
        <div class="mt-auto flex items-end justify-between gap-3 pt-5">
            <p class="text-sm text-muted">
                {{ __('site.common.from') }}
                <span class="block text-2xl font-semibold text-green-deep">{{ number_format((float) $room->base_price) }} <span class="text-sm font-medium">{{ __('site.common.sar') }}</span></span>
                <span class="text-xs">{{ __('site.common.per_night') }}</span>
            </p>
            <a href="{{ route('booking.create', ['room' => $room->slug]) }}" class="btn-primary px-5 py-2.5">{{ __('site.nav.book') }}</a>
        </div>
    </div>
</article>
