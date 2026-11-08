<x-layout :title="__('site.gallery.title')">
    <x-page-header :title="__('site.gallery.title')" :subtitle="__('site.gallery.subtitle')" />
    <section class="container-x py-16" x-data="{ open: null, images: @js($images->map(fn ($i) => ['src' => $i->url(), 'alt' => $i->tr('caption')])->values()) }"
             @keydown.escape.window="open = null">
        @if ($images->isEmpty())
            <div class="grid gap-4 sm:grid-cols-3">
                @for ($i = 0; $i < 3; $i++)
                    <div class="aspect-[4/3] overflow-hidden rounded-2xl"><x-media :alt="__('site.common.photo_soon')" /></div>
                @endfor
            </div>
            <p class="mt-8 text-center text-muted">{{ __('site.gallery.empty') }}</p>
        @else
            <div class="columns-2 gap-3 sm:columns-3 lg:columns-4 [&>*]:mb-3">
                @foreach ($images as $i => $image)
                    <button type="button" @click="open = {{ $i }}" class="block w-full overflow-hidden rounded-xl">
                        <img src="{{ $image->url() }}" alt="{{ $image->tr('caption') }}" class="w-full transition hover:scale-105" loading="lazy">
                    </button>
                @endforeach
            </div>
            <div x-show="open !== null" x-cloak x-transition.opacity @click.self="open = null"
                 class="fixed inset-0 z-50 flex items-center justify-center bg-black/85 p-4" role="dialog" aria-modal="true">
                <button type="button" @click="open = null" class="absolute end-4 top-4 text-white" aria-label="Close">{{ svg('heroicon-o-x-mark', 'h-8 w-8') }}</button>
                <template x-if="open !== null">
                    <figure class="max-h-full max-w-5xl">
                        <img :src="images[open].src" :alt="images[open].alt" class="max-h-[80vh] rounded-lg object-contain">
                        <figcaption x-text="images[open].alt" class="mt-3 text-center text-white/80"></figcaption>
                    </figure>
                </template>
            </div>
        @endif
    </section>
</x-layout>
