@php($allowed = '<p><br><strong><em><b><i><u><ul><ol><li><h2><h3><a><blockquote>')
<x-layout :title="__('site.about.title')">
    <x-page-header :title="__('site.about.title')" :subtitle="__('site.about.subtitle')" />
    <section class="container-x grid gap-12 py-16 lg:grid-cols-[1.4fr_1fr]">
        <div class="prose-noven text-lg">{!! strip_tags((string) $about, $allowed) !!}</div>
        <div class="space-y-6">
            <div class="flex justify-center rounded-2xl bg-green-mist p-10">
                <img src="{{ asset('brand/logo/svg/noven-logo-light.svg') }}" alt="NOVEN" class="h-48 w-auto" loading="lazy">
            </div>
            @if ($policies)
                <div class="rounded-2xl border border-green-line p-6">
                    <h2 class="mb-4 text-2xl text-green">{{ __('site.about.policies') }}</h2>
                    <div class="prose-noven text-sm">{!! strip_tags((string) $policies, $allowed) !!}</div>
                </div>
            @endif
        </div>
    </section>
    @if ($services->isNotEmpty())
        <section class="bg-green-mist py-16">
            <div class="container-x grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($services as $service)
                    <div class="rounded-2xl bg-white p-6 text-center">
                        <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-green-mist text-green">{{ svg('heroicon-o-'.$service->icon, 'h-6 w-6') }}</span>
                        <h3 class="mt-3 text-xl text-green">{{ $service->tr('title') }}</h3>
                    </div>
                @endforeach
            </div>
        </section>
    @endif
</x-layout>
