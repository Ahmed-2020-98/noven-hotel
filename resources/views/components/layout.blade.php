@props(['title' => null, 'description' => null, 'image' => null])
@php
    $siteName = $hotel?->tr('name') ?? 'NOVEN';
    $pageTitle = $title ? "{$title} | {$siteName}" : "{$siteName} — ".__('site.home.hero_title');
    $metaDescription = $description ?? __('site.meta.description');
    $ogImage = $image ?? asset('brand/logo/png/noven-avatar-1080.png');
    $routeName = request()->route()?->getName();
@endphp
<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ $metaDescription }}">
    <meta name="theme-color" content="#0F5247">
    <link rel="canonical" href="{{ url()->current() }}">
    <link rel="alternate" hreflang="{{ $locale }}" href="{{ url()->current() }}">
    <link rel="alternate" hreflang="{{ $alternateLocale }}" href="{{ $alternateUrl }}">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ $siteName }}">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $metaDescription }}">
    <meta property="og:image" content="{{ $ogImage }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:locale" content="{{ $locale === 'ar' ? 'ar_SA' : 'en_US' }}">
    <meta name="twitter:card" content="summary_large_image">

    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="icon" href="{{ asset('favicon-32.png') }}" sizes="32x32">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&family=Cormorant+Garamond:wght@500;600;700&family=IBM+Plex+Sans+Arabic:wght@400;500;600&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @if ($hotel)
        <script type="application/ld+json">{!! json_encode(array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'Hotel',
            'name' => $hotel->tr('name'),
            'url' => route('home'),
            'logo' => asset('brand/logo/png/noven-logo-light@3x.png'),
            'image' => $ogImage,
            'telephone' => $hotel->phone,
            'email' => $hotel->email,
            'priceRange' => 'SAR',
            'checkinTime' => substr((string) $hotel->check_in_time, 0, 5),
            'checkoutTime' => substr((string) $hotel->check_out_time, 0, 5),
            'address' => ['@type' => 'PostalAddress', 'streetAddress' => $hotel->tr('address'), 'addressLocality' => $hotel->tr('city'), 'addressCountry' => 'SA'],
            'geo' => $hotel->latitude ? ['@type' => 'GeoCoordinates', 'latitude' => $hotel->latitude, 'longitude' => $hotel->longitude] : null,
        ]), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
    @endif
    {{ $head ?? '' }}
</head>
<body class="flex min-h-screen flex-col">
    <a href="#main" class="sr-only focus:not-sr-only focus:absolute focus:start-4 focus:top-4 focus:z-50 focus:rounded-lg focus:bg-white focus:px-4 focus:py-2">Skip to content</a>

    @include('partials.header', ['routeName' => $routeName])

    <main id="main" class="flex-1">
        {{ $slot }}
    </main>

    @include('partials.footer')

    @if ($hotel?->whatsappNumber() && ! str_starts_with((string) $routeName, 'booking.'))
        <a href="https://wa.me/{{ $hotel->whatsappNumber() }}" target="_blank" rel="noopener"
           class="fixed bottom-5 end-5 z-40 flex h-14 w-14 items-center justify-center rounded-full bg-[#25D366] text-white shadow-lg shadow-black/20 transition hover:scale-105"
           aria-label="{{ __('site.common.whatsapp') }}">
            <svg viewBox="0 0 24 24" class="h-7 w-7" fill="currentColor" aria-hidden="true"><path d="M17.47 14.38c-.3-.15-1.75-.86-2.02-.96-.27-.1-.47-.15-.67.15-.2.3-.77.96-.94 1.16-.17.2-.35.22-.64.07-.3-.15-1.25-.46-2.38-1.47-.88-.79-1.47-1.76-1.65-2.06-.17-.3-.02-.46.13-.6.13-.14.3-.35.45-.52.15-.17.2-.3.3-.5.1-.2.05-.37-.03-.52-.07-.15-.67-1.6-.92-2.2-.24-.58-.49-.5-.67-.5h-.57c-.2 0-.52.07-.79.37-.27.3-1.04 1.02-1.04 2.48 0 1.46 1.07 2.88 1.21 3.08.15.2 2.1 3.2 5.08 4.48.71.31 1.26.49 1.7.63.71.23 1.36.2 1.87.12.57-.09 1.75-.72 2-1.41.25-.69.25-1.29.17-1.41-.07-.12-.27-.2-.57-.35M12.05 21.5h-.01a9.4 9.4 0 0 1-4.8-1.31l-.34-.2-3.57.94.95-3.48-.22-.36a9.4 9.4 0 0 1-1.44-5.02c0-5.2 4.23-9.43 9.44-9.43a9.38 9.38 0 0 1 9.43 9.44c0 5.2-4.24 9.43-9.44 9.43m8.03-17.46A11.27 11.27 0 0 0 12.05.72C5.8.72.7 5.8.7 12.06c0 2 .52 3.95 1.52 5.67L.6 23.6l6-1.57a11.3 11.3 0 0 0 5.43 1.38h.01c6.25 0 11.34-5.09 11.34-11.34 0-3.03-1.18-5.88-3.32-8.02"/></svg>
        </a>
    @endif
</body>
</html>
