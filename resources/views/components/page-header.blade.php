@props(['title', 'subtitle' => null])
<section class="pattern-dark">
    <div class="bg-gradient-to-b from-green/70 to-green-deep/90">
        <div class="container-x py-14 text-center text-white sm:py-20">
            <h1 class="text-4xl sm:text-5xl">{{ $title }}</h1>
            @if ($subtitle)
                <p class="mx-auto mt-4 max-w-2xl text-white/80">{{ $subtitle }}</p>
            @endif
            {{ $slot }}
        </div>
    </div>
</section>
