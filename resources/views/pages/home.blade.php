@php
    use App\Models\Setting;
    $heroImage = Setting::read('hero_image') ? \Illuminate\Support\Facades\Storage::disk('public')->url(Setting::read('hero_image')) : null;
@endphp
<x-layout :image="$heroImage">
    {{-- Hero --}}
    <section class="relative isolate overflow-hidden bg-green-deep">
        @if ($heroImage)
            <img src="{{ $heroImage }}" alt="" class="absolute inset-0 -z-10 h-full w-full object-cover" fetchpriority="high">
            <div class="absolute inset-0 -z-10 bg-gradient-to-t from-green-deep via-green-deep/60 to-green-deep/30"></div>
        @else
            <div class="pattern-dark absolute inset-0 -z-10 opacity-60"></div>
            <div class="absolute inset-0 -z-10 bg-gradient-to-b from-green/40 to-green-deep"></div>
        @endif

        <div class="container-x flex min-h-[78vh] flex-col items-center justify-center py-20 text-center text-white">
            @unless ($heroImage)
                <img src="{{ asset('brand/logo/svg/noven-symbol-dark.svg') }}" alt="" class="mb-6 h-24 w-auto sm:h-28" fetchpriority="high">
            @endunless
            <h1 class="max-w-3xl text-4xl leading-tight sm:text-6xl">{{ Setting::text('hero_title') ?: __('site.home.hero_title') }}</h1>
            <p class="mt-5 max-w-2xl text-lg text-white/80">{{ Setting::text('hero_subtitle') ?: __('site.home.hero_subtitle') }}</p>

            {{-- Quick availability search --}}
            <form action="{{ route('booking.create') }}" method="get" x-data="{ ci: '{{ today()->toDateString() }}', co: '{{ today()->addDay()->toDateString() }}' }"
                  class="mt-10 grid w-full max-w-4xl gap-3 rounded-2xl bg-white p-3 text-start text-green-deep shadow-2xl shadow-black/20 sm:grid-cols-2 lg:grid-cols-[1fr_1fr_1fr_0.7fr_auto]">
                <label class="block">
                    <span class="label px-1 text-xs text-muted">{{ __('site.booking.room_type') }}</span>
                    <select name="room" class="field py-2.5">
                        <option value="">{{ __('site.booking.choose_room') }}</option>
                        @foreach ($rooms as $room)
                            <option value="{{ $room->slug }}">{{ $room->tr('name') }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="block">
                    <span class="label px-1 text-xs text-muted">{{ __('site.common.check_in') }}</span>
                    <input type="date" name="check_in" x-model="ci" min="{{ today()->toDateString() }}" class="field py-2.5"
                           @change="if (co <= ci) { const d = new Date(ci + 'T00:00:00'); d.setDate(d.getDate() + 1); co = d.toISOString().slice(0, 10); }">
                </label>
                <label class="block">
                    <span class="label px-1 text-xs text-muted">{{ __('site.common.check_out') }}</span>
                    <input type="date" name="check_out" x-model="co" :min="ci" class="field py-2.5">
                </label>
                <label class="block">
                    <span class="label px-1 text-xs text-muted">{{ __('site.common.adults') }}</span>
                    <input type="number" name="adults" value="2" min="1" max="10" class="field py-2.5">
                </label>
                <button type="submit" class="btn-gold self-end py-3 sm:col-span-2 lg:col-span-1">{{ __('site.common.search') }}</button>
            </form>
        </div>
    </section>

    {{-- Intro --}}
    <section class="container-x grid items-center gap-10 py-20 lg:grid-cols-2">
        <div>
            <p class="eyebrow mb-3">{{ __('site.about.subtitle') }}</p>
            <h2 class="text-3xl text-green sm:text-4xl">{{ $hotel?->tr('name') }}</h2>
            <div class="prose-noven mt-5 line-clamp-6">{!! strip_tags((string) Setting::text('about'), '<p><br><strong><em>') !!}</div>
            <a href="{{ route('about') }}" class="btn-outline mt-7">{{ __('site.nav.about') }} {{ svg('heroicon-o-arrow-left', 'h-4 w-4 ltr:rotate-180') }}</a>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div class="rounded-2xl bg-green-mist p-6 text-center">
                {{ svg('heroicon-o-clock', 'mx-auto h-8 w-8 text-gold') }}
                <p class="mt-3 text-sm text-muted">{{ __('site.common.check_in_time') }}</p>
                <p class="text-2xl font-semibold" dir="ltr">{{ substr((string) $hotel?->check_in_time, 0, 5) }}</p>
            </div>
            <div class="rounded-2xl bg-green-mist p-6 text-center">
                {{ svg('heroicon-o-key', 'mx-auto h-8 w-8 text-gold') }}
                <p class="mt-3 text-sm text-muted">{{ __('site.common.check_out_time') }}</p>
                <p class="text-2xl font-semibold" dir="ltr">{{ substr((string) $hotel?->check_out_time, 0, 5) }}</p>
            </div>
            <div class="col-span-2 rounded-2xl bg-green p-6 text-center text-white">
                <p class="text-sm text-white/75">{{ __('site.nav.rooms') }}</p>
                <p class="mt-1 text-3xl font-semibold">{{ $rooms->count() }}</p>
            </div>
        </div>
    </section>

    {{-- Rooms --}}
    <section class="pattern-light py-20">
        <div class="container-x">
            <x-section-title :title="__('site.home.rooms_title')" :subtitle="__('site.home.rooms_subtitle')" />
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($rooms->take(6) as $room)
                    <x-room-card :room="$room" />
                @endforeach
            </div>
            @if ($rooms->count() > 6)
                <div class="mt-10 text-center"><a href="{{ route('rooms.index') }}" class="btn-outline">{{ __('site.common.view_all') }}</a></div>
            @endif
        </div>
    </section>

    {{-- Services --}}
    @if ($services->isNotEmpty())
        <section class="container-x py-20">
            <x-section-title :title="__('site.home.services_title')" />
            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($services as $service)
                    <div class="flex gap-4 rounded-2xl border border-green-line p-6">
                        <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-green-mist text-green">{{ svg('heroicon-o-'.$service->icon, 'h-6 w-6') }}</span>
                        <div>
                            <h3 class="text-xl text-green">{{ $service->tr('title') }}</h3>
                            <p class="mt-1 text-sm leading-6 text-green-deep/70">{{ $service->tr('description') }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    {{-- Gallery strip --}}
    @if ($gallery->isNotEmpty())
        <section class="pb-20">
            <div class="container-x">
                <x-section-title :title="__('site.home.gallery_title')" />
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                    @foreach ($gallery as $image)
                        <a href="{{ route('gallery') }}" @class(['overflow-hidden rounded-xl', 'sm:col-span-2 sm:row-span-2' => $loop->first])>
                            <img src="{{ $image->url() }}" alt="{{ $image->tr('caption') }}" class="aspect-square h-full w-full object-cover transition hover:scale-105" loading="lazy">
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- FAQ --}}
    @if ($faqs->isNotEmpty())
        <section class="bg-green-mist py-20">
            <div class="container-x">
                <x-section-title :title="__('site.common.faq')" />
                <x-faq-list :faqs="$faqs" />
            </div>
        </section>
    @endif

    {{-- CTA --}}
    <section class="pattern-dark">
        <div class="bg-green-deep/80">
            <div class="container-x flex flex-col items-center gap-6 py-16 text-center text-white">
                <h2 class="text-3xl sm:text-4xl">{{ __('site.home.cta_title') }}</h2>
                <p class="max-w-xl text-white/80">{{ __('site.home.cta_text') }}</p>
                <div class="flex flex-wrap justify-center gap-3">
                    <a href="{{ route('booking.create') }}" class="btn-gold">{{ __('site.nav.book') }}</a>
                    @if ($hotel?->whatsappNumber())
                        <a href="https://wa.me/{{ $hotel->whatsappNumber() }}" target="_blank" rel="noopener" class="btn-ghost-light">{{ __('site.common.whatsapp') }}</a>
                    @endif
                    @if ($hotel?->phone)
                        <a href="tel:{{ preg_replace('/\s+/', '', $hotel->phone) }}" class="btn-ghost-light">{{ __('site.common.call') }}</a>
                    @endif
                </div>
            </div>
        </div>
    </section>

    {{-- Map --}}
    @if ($hotel?->map_embed_url)
        <section class="container-x py-20">
            <x-section-title :title="__('site.home.location_title')" :subtitle="$hotel->tr('address')" />
            <div class="overflow-hidden rounded-2xl border border-green-line">
                <iframe src="{{ $hotel->map_embed_url }}" class="h-80 w-full sm:h-96" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="{{ __('site.home.location_title') }}"></iframe>
            </div>
            @if ($hotel->map_link)
                <div class="mt-5 text-center"><a href="{{ $hotel->map_link }}" target="_blank" rel="noopener" class="btn-outline">{{ svg('heroicon-o-map-pin', 'h-4 w-4') }} {{ __('site.common.directions') }}</a></div>
            @endif
        </section>
    @endif
</x-layout>
