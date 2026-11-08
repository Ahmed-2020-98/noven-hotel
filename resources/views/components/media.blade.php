@props(['src' => null, 'alt' => '', 'eager' => false])
{{-- Image with a branded placeholder (pattern + sun symbol) until real photos are uploaded. --}}
@if ($src)
    <img src="{{ $src }}" alt="{{ $alt }}" {{ $attributes->merge(['class' => 'h-full w-full object-cover']) }}
         loading="{{ $eager ? 'eager' : 'lazy' }}" decoding="async">
@else
    <div {{ $attributes->merge(['class' => 'pattern-dark relative flex h-full w-full items-center justify-center']) }} role="img" aria-label="{{ $alt }}">
        <div class="absolute inset-0 bg-gradient-to-br from-green/40 to-green-deep/80"></div>
        <img src="{{ asset('brand/logo/svg/noven-symbol-dark.svg') }}" alt="" class="relative h-1/3 max-h-28 w-auto opacity-90" loading="lazy">
    </div>
@endif
