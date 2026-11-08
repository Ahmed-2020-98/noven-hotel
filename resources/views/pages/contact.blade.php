<x-layout :title="__('site.contact.title')">
    <x-page-header :title="__('site.contact.title')" :subtitle="__('site.contact.subtitle')" />
    <section class="container-x grid gap-10 py-16 lg:grid-cols-[1fr_1.3fr]">
        <div class="space-y-4">
            @if ($hotel?->tr('address'))
                <div class="flex gap-4 rounded-2xl border border-green-line p-5">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-green-mist text-green">{{ svg('heroicon-o-map-pin', 'h-5 w-5') }}</span>
                    <div><p class="text-sm text-muted">{{ __('site.contact.address') }}</p><p class="font-medium">{{ $hotel->tr('address') }}</p>
                        @if ($hotel->map_link)<a href="{{ $hotel->map_link }}" target="_blank" rel="noopener" class="text-sm text-green underline">{{ __('site.common.directions') }}</a>@endif
                    </div>
                </div>
            @endif
            @if ($hotel?->phone)
                <a href="tel:{{ preg_replace('/\s+/', '', $hotel->phone) }}" class="flex gap-4 rounded-2xl border border-green-line p-5 hover:border-green">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-green-mist text-green">{{ svg('heroicon-o-phone', 'h-5 w-5') }}</span>
                    <div><p class="text-sm text-muted">{{ __('site.common.call') }}</p><p class="font-medium" dir="ltr">{{ $hotel->phone }}</p></div>
                </a>
            @endif
            @if ($hotel?->whatsappNumber())
                <a href="https://wa.me/{{ $hotel->whatsappNumber() }}" target="_blank" rel="noopener" class="flex gap-4 rounded-2xl border border-green-line p-5 hover:border-green">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-green-mist text-green">{{ svg('heroicon-o-chat-bubble-left-right', 'h-5 w-5') }}</span>
                    <div><p class="text-sm text-muted">{{ __('site.common.whatsapp') }}</p><p class="font-medium" dir="ltr">{{ $hotel->whatsapp }}</p></div>
                </a>
            @endif
            @if ($hotel?->email)
                <a href="mailto:{{ $hotel->email }}" class="flex gap-4 rounded-2xl border border-green-line p-5 hover:border-green">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-green-mist text-green">{{ svg('heroicon-o-envelope', 'h-5 w-5') }}</span>
                    <div><p class="text-sm text-muted">{{ __('site.contact.email') }}</p><p class="font-medium">{{ $hotel->email }}</p></div>
                </a>
            @endif
        </div>

        <form method="post" action="{{ route('contact.store') }}" class="rounded-2xl border border-green-line p-6 sm:p-8">
            @csrf
            <div class="hidden" aria-hidden="true"><input type="text" name="website" tabindex="-1" autocomplete="off"></div>
            <h2 class="mb-6 text-2xl text-green">{{ __('site.contact.form_title') }}</h2>
            @if (session('status'))
                <div class="mb-5 rounded-xl bg-green-mist p-4 text-sm text-green" role="status">{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div class="mb-5 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800" role="alert">{{ $errors->first() }}</div>
            @endif
            <div class="grid gap-4 sm:grid-cols-2">
                <label class="sm:col-span-2"><span class="label">{{ __('site.contact.name') }}</span><input name="name" value="{{ old('name') }}" class="field" required maxlength="120" autocomplete="name"></label>
                <label><span class="label">{{ __('site.contact.phone') }}</span><input type="tel" name="phone" value="{{ old('phone') }}" class="field" dir="ltr" autocomplete="tel"></label>
                <label><span class="label">{{ __('site.contact.email') }}</span><input type="email" name="email" value="{{ old('email') }}" class="field" dir="ltr" autocomplete="email"></label>
                <label class="sm:col-span-2"><span class="label">{{ __('site.contact.subject') }}</span><input name="subject" value="{{ old('subject') }}" class="field" maxlength="150"></label>
                <label class="sm:col-span-2"><span class="label">{{ __('site.contact.message') }}</span><textarea name="message" rows="5" class="field" required maxlength="3000">{{ old('message') }}</textarea></label>
            </div>
            <button type="submit" class="btn-primary mt-6">{{ __('site.contact.send') }}</button>
        </form>
    </section>

    @if ($hotel?->map_embed_url)
        <iframe src="{{ $hotel->map_embed_url }}" class="block h-80 w-full border-0" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="{{ __('site.home.location_title') }}"></iframe>
    @endif
</x-layout>
