@php
    $socials = array_filter([
        'Instagram' => \App\Models\Setting::read('social_instagram'),
        'X' => \App\Models\Setting::read('social_x'),
        'Snapchat' => \App\Models\Setting::read('social_snapchat'),
        'TikTok' => \App\Models\Setting::read('social_tiktok'),
    ]);
@endphp
<footer class="pattern-dark text-white">
    <div class="bg-green-deep/85">
        <div class="container-x grid gap-10 py-14 sm:grid-cols-2 lg:grid-cols-4">
            <div class="space-y-4">
                <img src="{{ asset('brand/logo/svg/noven-logo-dark.svg') }}" alt="NOVEN" class="h-24 w-auto" loading="lazy" width="80" height="96">
                <p class="text-sm leading-7 text-white/75">{{ __('site.home.hero_subtitle') }}</p>
            </div>

            <div>
                <h3 class="mb-4 text-lg text-gold">{{ __('site.common.quick_links') }}</h3>
                <ul class="space-y-2.5 text-sm text-white/80">
                    <li><a class="hover:text-gold" href="{{ route('rooms.index') }}">{{ __('site.nav.rooms') }}</a></li>
                    <li><a class="hover:text-gold" href="{{ route('services') }}">{{ __('site.nav.services') }}</a></li>
                    <li><a class="hover:text-gold" href="{{ route('gallery') }}">{{ __('site.nav.gallery') }}</a></li>
                    <li><a class="hover:text-gold" href="{{ route('about') }}">{{ __('site.nav.about') }}</a></li>
                    <li><a class="hover:text-gold" href="{{ route('booking.create') }}">{{ __('site.nav.book') }}</a></li>
                </ul>
            </div>

            <div>
                <h3 class="mb-4 text-lg text-gold">{{ __('site.common.contact_info') }}</h3>
                <ul class="space-y-3 text-sm text-white/80">
                    @if ($hotel?->tr('address'))
                        <li class="flex gap-2.5">{{ svg('heroicon-o-map-pin', 'h-5 w-5 shrink-0 text-gold') }}<span>{{ $hotel->tr('address') }}</span></li>
                    @endif
                    @if ($hotel?->phone)
                        <li class="flex gap-2.5">{{ svg('heroicon-o-phone', 'h-5 w-5 shrink-0 text-gold') }}<a href="tel:{{ preg_replace('/\s+/', '', $hotel->phone) }}" dir="ltr" class="hover:text-gold">{{ $hotel->phone }}</a></li>
                    @endif
                    @if ($hotel?->email)
                        <li class="flex gap-2.5">{{ svg('heroicon-o-envelope', 'h-5 w-5 shrink-0 text-gold') }}<a href="mailto:{{ $hotel->email }}" class="hover:text-gold">{{ $hotel->email }}</a></li>
                    @endif
                </ul>
            </div>

            <div>
                <h3 class="mb-4 text-lg text-gold">{{ __('site.common.check_in_time') }} / {{ __('site.common.check_out_time') }}</h3>
                <p class="text-sm text-white/80">{{ __('site.common.check_in_time') }}: <span dir="ltr">{{ substr((string) $hotel?->check_in_time, 0, 5) }}</span></p>
                <p class="mt-1 text-sm text-white/80">{{ __('site.common.check_out_time') }}: <span dir="ltr">{{ substr((string) $hotel?->check_out_time, 0, 5) }}</span></p>
                @if ($socials)
                    <h3 class="mt-6 mb-3 text-lg text-gold">{{ __('site.common.follow_us') }}</h3>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($socials as $name => $url)
                            <a href="{{ $url }}" target="_blank" rel="noopener" class="rounded-full border border-white/25 px-3 py-1.5 text-xs hover:border-gold hover:text-gold">{{ $name }}</a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <div class="border-t border-white/10">
            <div class="container-x flex flex-col items-center justify-between gap-3 py-5 text-xs text-white/60 sm:flex-row">
                <p>© {{ date('Y') }} {{ $hotel?->tr('name') ?? 'NOVEN' }} — {{ __('site.common.all_rights') }}</p>
                <div class="flex gap-4">
                    <a href="{{ route('privacy') }}" class="hover:text-gold">{{ __('site.nav.privacy') }}</a>
                    <a href="{{ route('terms') }}" class="hover:text-gold">{{ __('site.nav.terms') }}</a>
                </div>
            </div>
        </div>
    </div>
</footer>
