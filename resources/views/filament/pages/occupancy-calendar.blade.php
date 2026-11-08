<x-filament-panels::page>
    {{-- Scoped styles: Filament's prebuilt CSS does not include Tailwind classes from custom views. --}}
    <style>
        .occ-toolbar { display: flex; flex-wrap: wrap; align-items: center; gap: .5rem; }
        .occ-hint { font-size: .8rem; color: rgb(107 114 128); }
        .occ-wrap { overflow-x: auto; border-radius: .75rem; border: 1px solid rgb(229 231 235); background: #fff; }
        .dark .occ-wrap { border-color: rgb(255 255 255 / .1); background: rgb(17 24 39); }
        .occ-table { width: 100%; border-collapse: collapse; text-align: center; font-size: .875rem; }
        .occ-table th { padding: .6rem .4rem; font-weight: 500; white-space: nowrap; background: rgb(249 250 251); }
        .dark .occ-table th { background: rgb(255 255 255 / .04); }
        .occ-table th small { display: block; font-size: .7rem; color: rgb(107 114 128); }
        .occ-table th.is-today { color: #0F5247; font-weight: 700; }
        .dark .occ-table th.is-today { color: #D4AF37; }
        .occ-table td { padding: .3rem; border-top: 1px solid rgb(243 244 246); }
        .dark .occ-table td { border-top-color: rgb(255 255 255 / .05); }
        .occ-room { position: sticky; inset-inline-start: 0; text-align: start !important; padding-inline: .9rem !important; font-weight: 600; white-space: nowrap; background: inherit; }
        .occ-room small { color: rgb(156 163 175); font-weight: 400; }
        .occ-cell { display: inline-flex; align-items: center; justify-content: center; width: 2.5rem; height: 2rem; border-radius: .4rem; font-weight: 700; }
        .occ-full { background: #fee2e2; color: #b91c1c; }
        .occ-low { background: #fef3c7; color: #b45309; }
        .occ-ok { background: #dcfce7; color: #15803d; }
        .dark .occ-full { background: rgb(239 68 68 / .2); color: #fca5a5; }
        .dark .occ-low { background: rgb(245 158 11 / .2); color: #fcd34d; }
        .dark .occ-ok { background: rgb(34 197 94 / .2); color: #86efac; }
    </style>

    <div class="occ-toolbar">
        <x-filament::button color="gray" icon="heroicon-o-chevron-right" wire:click="shift(-{{ \App\Filament\Pages\OccupancyCalendar::DAYS }})">السابق</x-filament::button>
        <x-filament::button color="gray" wire:click="today">اليوم</x-filament::button>
        <x-filament::button color="gray" icon="heroicon-o-chevron-left" icon-position="after" wire:click="shift({{ \App\Filament\Pages\OccupancyCalendar::DAYS }})">التالي</x-filament::button>
        <div style="width: 11rem">
            <x-filament::input.wrapper>
                <x-filament::input type="date" wire:model.live="start" />
            </x-filament::input.wrapper>
        </div>
        <span class="occ-hint">الرقم = عدد الغرف المتاحة · أحمر: ممتلئ · أصفر: أقل من 30% متاح</span>
    </div>

    <div class="occ-wrap">
        <table class="occ-table">
            <thead>
                <tr>
                    <th class="occ-room">نوع الغرفة</th>
                    @foreach ($days as $day)
                        <th @class(['is-today' => $day->isToday()])>
                            <small>{{ $day->locale('ar')->translatedFormat('D') }}</small>
                            {{ $day->format('d/m') }}
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse ($roomTypes as $roomType)
                    <tr>
                        <td class="occ-room">{{ $roomType->name_ar }} <small>({{ $roomType->units_count }})</small></td>
                        @foreach ($days as $day)
                            @php
                                $free = $matrix[$roomType->id][$day->toDateString()] ?? 0;
                                $ratio = $roomType->units_count > 0 ? $free / $roomType->units_count : 0;
                            @endphp
                            <td>
                                <span @class([
                                    'occ-cell',
                                    'occ-full' => $free <= 0,
                                    'occ-low' => $free > 0 && $ratio <= 0.3,
                                    'occ-ok' => $ratio > 0.3,
                                ])>{{ max(0, $free) }}</span>
                            </td>
                        @endforeach
                    </tr>
                @empty
                    <tr><td colspan="{{ count($days) + 1 }}" style="padding: 1.5rem">لا توجد أنواع غرف مفعلة.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-filament-panels::page>
