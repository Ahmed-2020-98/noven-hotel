<x-layout :title="$title">
    <x-page-header :title="$title" />
    <section class="container-x max-w-3xl py-16">
        <div class="prose-noven">{!! strip_tags((string) $content, '<p><br><strong><em><b><i><u><ul><ol><li><h2><h3><a><blockquote>') !!}</div>
    </section>
</x-layout>
