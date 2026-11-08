@props(['faqs'])
<div class="mx-auto max-w-3xl divide-y divide-green-line rounded-2xl border border-green-line bg-white">
    @foreach ($faqs as $faq)
        <details class="group p-5 [&_summary::-webkit-details-marker]:hidden">
            <summary class="flex cursor-pointer items-center justify-between gap-4 font-semibold text-green-deep">
                {{ $faq->tr('question') }}
                <span class="text-gold transition group-open:rotate-45">{{ svg('heroicon-o-plus', 'h-5 w-5') }}</span>
            </summary>
            <p class="mt-3 leading-7 text-green-deep/75">{{ $faq->tr('answer') }}</p>
        </details>
    @endforeach
</div>
