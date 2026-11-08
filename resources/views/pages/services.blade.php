<x-layout :title="__('site.services.title')">
    <x-page-header :title="__('site.services.title')" :subtitle="__('site.services.subtitle')" />
    <section class="container-x py-16">
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($services as $service)
                <article class="overflow-hidden rounded-2xl border border-green-line bg-white">
                    @if ($service->imageUrl())
                        <div class="aspect-[16/10]"><x-media :src="$service->imageUrl()" :alt="$service->tr('title')" /></div>
                    @endif
                    <div class="p-6">
                        <span class="flex h-12 w-12 items-center justify-center rounded-full bg-green-mist text-green">{{ svg('heroicon-o-'.$service->icon, 'h-6 w-6') }}</span>
                        <h2 class="mt-4 text-2xl text-green">{{ $service->tr('title') }}</h2>
                        <p class="mt-2 leading-7 text-green-deep/75">{{ $service->tr('description') }}</p>
                    </div>
                </article>
            @endforeach
        </div>
    </section>
    @if ($faqs->isNotEmpty())
        <section class="bg-green-mist py-16">
            <div class="container-x">
                <x-section-title :title="__('site.common.faq')" />
                <x-faq-list :faqs="$faqs" />
            </div>
        </section>
    @endif
</x-layout>
