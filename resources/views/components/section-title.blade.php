@props(['title', 'subtitle' => null, 'align' => 'center'])
<div @class(['mb-10 space-y-3', 'text-center' => $align === 'center'])>
    <h2 class="text-3xl text-green sm:text-4xl">{{ $title }}</h2>
    <div @class(['divider-gold', 'justify-start! mx-0!' => $align !== 'center'])>
        <span class="block h-2 w-2 rotate-45 bg-gold"></span>
    </div>
    @if ($subtitle)
        <p class="text-muted">{{ $subtitle }}</p>
    @endif
</div>
