@php
    $images = array_values(array_filter([$room->coverUrl(), ...$room->galleryUrls()]));
@endphp
<x-layout :title="$room->tr('name')" :description="$room->tr('summary')" :image="$room->coverUrl()">
    <section class="container-x pt-8 pb-16">
        <nav class="mb-6 text-sm text-muted" aria-label="Breadcrumb">
            <a href="{{ route('home') }}" class="hover:text-green">{{ __('site.nav.home') }}</a>
            <span class="mx-2">/</span>
            <a href="{{ route('rooms.index') }}" class="hover:text-green">{{ __('site.nav.rooms') }}</a>
            <span class="mx-2">/</span>
            <span class="text-green-deep">{{ $room->tr('name') }}</span>
        </nav>

        <div class="grid gap-10 lg:grid-cols-[1fr_380px]">
            <div>
                {{-- Gallery --}}
                <div x-data="{ active: 0, images: @js($images) }" class="space-y-3">
                    <div class="aspect-[16/10] overflow-hidden rounded-2xl">
                        @if ($images)
                            <img :src="images[active]" src="{{ $images[0] }}" alt="{{ $room->tr('name') }}" class="h-full w-full object-cover" fetchpriority="high">
                        @else
                            <x-media :alt="$room->tr('name')" />
                        @endif
                    </div>
                    @if (count($images) > 1)
                        <div class="grid grid-cols-5 gap-2 sm:grid-cols-6">
                            @foreach ($images as $i => $src)
                                <button type="button" @click="active = {{ $i }}" class="aspect-square overflow-hidden rounded-lg ring-2 ring-transparent transition"
                                        :class="active === {{ $i }} ? 'ring-gold' : 'opacity-75 hover:opacity-100'">
                                    <img src="{{ $src }}" alt="" class="h-full w-full object-cover" loading="lazy">
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>

                <h1 class="mt-8 text-4xl text-green sm:text-5xl">{{ $room->tr('name') }}</h1>
                <div class="mt-5 grid grid-cols-3 gap-3">
                    <div class="rounded-xl bg-green-mist p-4">
                        <p class="text-xs text-muted">{{ __('site.rooms.size') }}</p>
                        <p class="mt-1 font-semibold">{{ $room->size_sqm ? $room->size_sqm.' '.__('site.common.sqm') : '—' }}</p>
                    </div>
                    <div class="rounded-xl bg-green-mist p-4">
                        <p class="text-xs text-muted">{{ __('site.rooms.capacity') }}</p>
                        <p class="mt-1 font-semibold">{{ __('site.common.guests', ['count' => $room->maxGuests()]) }}</p>
                    </div>
                    <div class="rounded-xl bg-green-mist p-4">
                        <p class="text-xs text-muted">{{ __('site.rooms.beds') }}</p>
                        <p class="mt-1 font-semibold">{{ $room->tr('beds') ?: '—' }}</p>
                    </div>
                </div>

                @if ($room->tr('description'))
                    <div class="prose-noven mt-8 text-lg">{!! nl2br(e($room->tr('description'))) !!}</div>
                @endif

                @if ($room->amenities->isNotEmpty())
                    <h2 class="mt-10 text-2xl text-green">{{ __('site.rooms.amenities') }}</h2>
                    <ul class="mt-5 grid gap-3 sm:grid-cols-2">
                        @foreach ($room->amenities as $amenity)
                            <li class="flex items-center gap-3">
                                <span class="flex h-9 w-9 items-center justify-center rounded-full bg-green-mist text-green">{{ svg('heroicon-o-'.$amenity->icon, 'h-5 w-5') }}</span>
                                {{ $amenity->tr('name') }}
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            {{-- Booking box --}}
            <aside class="lg:sticky lg:top-24 lg:self-start">
                <form action="{{ route('booking.create') }}" method="get" class="rounded-2xl border border-green-line bg-white p-6 shadow-lg shadow-green/5"
                      x-data="{ ci: '{{ today()->toDateString() }}', co: '{{ today()->addDay()->toDateString() }}' }">
                    <input type="hidden" name="room" value="{{ $room->slug }}">
                    <p class="text-sm text-muted">{{ __('site.common.from') }}</p>
                    <p class="text-3xl font-semibold">{{ number_format((float) $room->base_price) }} <span class="text-base font-medium">{{ __('site.common.sar') }}</span> <span class="text-sm font-normal text-muted">{{ __('site.common.per_night') }}</span></p>
                    <div class="mt-5 grid grid-cols-2 gap-3">
                        <label><span class="label text-xs">{{ __('site.common.check_in') }}</span>
                            <input type="date" name="check_in" x-model="ci" min="{{ today()->toDateString() }}" class="field px-3 py-2.5 text-sm"
                                   @change="if (co <= ci) { const d = new Date(ci + 'T00:00:00'); d.setDate(d.getDate() + 1); co = d.toISOString().slice(0, 10); }"></label>
                        <label><span class="label text-xs">{{ __('site.common.check_out') }}</span>
                            <input type="date" name="check_out" x-model="co" :min="ci" class="field px-3 py-2.5 text-sm"></label>
                        <label><span class="label text-xs">{{ __('site.common.adults') }}</span>
                            <input type="number" name="adults" value="2" min="1" max="{{ $room->max_adults }}" class="field px-3 py-2.5 text-sm"></label>
                        <label><span class="label text-xs">{{ __('site.common.children') }}</span>
                            <input type="number" name="children" value="0" min="0" max="{{ $room->max_children }}" class="field px-3 py-2.5 text-sm"></label>
                    </div>
                    <button type="submit" class="btn-primary mt-5 w-full">{{ __('site.rooms.book_this') }}</button>
                    <p class="mt-3 text-center text-xs text-muted">{{ __('site.booking.estimate_note') }}</p>
                </form>
            </aside>
        </div>
    </section>

    @if ($others->isNotEmpty())
        <section class="bg-green-mist py-16">
            <div class="container-x">
                <x-section-title :title="__('site.rooms.other_rooms')" />
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($others as $other)
                        <x-room-card :room="$other" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif
</x-layout>
