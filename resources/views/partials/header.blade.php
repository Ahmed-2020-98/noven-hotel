@php
    $links = [
        'home' => __('site.nav.home'),
        'rooms.index' => __('site.nav.rooms'),
        'services' => __('site.nav.services'),
        'gallery' => __('site.nav.gallery'),
        'about' => __('site.nav.about'),
        'contact' => __('site.nav.contact'),
    ];
    $isActive = fn (string $name) => $routeName === $name || ($name === 'rooms.index' && $routeName === 'rooms.show');
@endphp
<header x-data="{ open: false }" class="sticky top-0 z-30 border-b border-green-line/70 bg-white/95 backdrop-blur">
    <div class="container-x flex h-18 items-center justify-between gap-4">
        <a href="{{ route('home') }}" class="shrink-0" aria-label="NOVEN">
            <img src="{{ asset('brand/logo/svg/noven-horizontal-light.svg') }}" alt="NOVEN" class="h-10 w-auto sm:h-11" width="160" height="44">
        </a>

        <nav class="hidden items-center gap-1 lg:flex" aria-label="Main">
            @foreach ($links as $name => $label)
                <a href="{{ route($name) }}" @class([
                    'rounded-full px-3.5 py-2 text-sm font-medium transition',
                    'bg-green-mist text-green' => $isActive($name),
                    'text-green-deep/80 hover:text-green' => ! $isActive($name),
                ])>{{ $label }}</a>
            @endforeach
        </nav>

        <div class="flex items-center gap-2">
            <a href="{{ $alternateUrl }}" hreflang="{{ $alternateLocale }}" lang="{{ $alternateLocale }}"
               class="hidden rounded-full px-3 py-2 text-sm font-semibold text-green hover:bg-green-mist sm:inline-flex">{{ __('site.nav.switch') }}</a>
            <a href="{{ route('booking.create') }}" class="btn-primary hidden px-5 py-2.5 sm:inline-flex">{{ __('site.nav.book') }}</a>
            <button type="button" @click="open = !open" class="inline-flex h-11 w-11 items-center justify-center rounded-full text-green hover:bg-green-mist lg:hidden"
                    :aria-expanded="open" aria-label="{{ __('site.nav.menu') }}">
                <span x-show="!open">{{ svg('heroicon-o-bars-3', 'h-6 w-6') }}</span>
                <span x-show="open" x-cloak>{{ svg('heroicon-o-x-mark', 'h-6 w-6') }}</span>
            </button>
        </div>
    </div>

    <div x-show="open" x-cloak x-transition.opacity class="border-t border-green-line bg-white lg:hidden">
        <nav class="container-x flex flex-col py-3" aria-label="Mobile">
            @foreach ($links as $name => $label)
                <a href="{{ route($name) }}" @class([
                    'rounded-xl px-3 py-3 text-base font-medium',
                    'bg-green-mist text-green' => $isActive($name),
                    'text-green-deep' => ! $isActive($name),
                ])>{{ $label }}</a>
            @endforeach
            <div class="mt-3 flex gap-2 border-t border-green-line pt-4">
                <a href="{{ route('booking.create') }}" class="btn-primary flex-1">{{ __('site.nav.book') }}</a>
                <a href="{{ $alternateUrl }}" class="btn-outline" lang="{{ $alternateLocale }}">{{ __('site.nav.switch') }}</a>
            </div>
        </nav>
    </div>
</header>
