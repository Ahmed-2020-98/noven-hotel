<x-layout :title="__('site.rooms.title')">
    <x-page-header :title="__('site.rooms.title')" :subtitle="__('site.rooms.subtitle')" />
    <section class="container-x py-16">
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($rooms as $room)
                <x-room-card :room="$room" />
            @endforeach
        </div>
    </section>
</x-layout>
