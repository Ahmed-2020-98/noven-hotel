<?php

namespace App\Filament\Widgets;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\RoomType;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class BookingStats extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $today = today()->toDateString();
        $totalUnits = (int) RoomType::where('is_active', true)->sum('units_count');
        $occupiedTonight = (int) Booking::holding()->where('status', '!=', BookingStatus::Pending)
            ->where('check_in', '<=', $today)->where('check_out', '>', $today)->sum('rooms');
        $monthRevenue = (float) Booking::whereIn('status', [BookingStatus::Confirmed, BookingStatus::CheckedIn, BookingStatus::CheckedOut])
            ->whereBetween('check_in', [now()->startOfMonth(), now()->endOfMonth()])->sum('total');

        return [
            Stat::make('طلبات تنتظر التأكيد', Booking::where('status', BookingStatus::Pending)->count())
                ->color('warning')->icon('heroicon-o-bell-alert'),
            Stat::make('وصول اليوم', Booking::whereDate('check_in', $today)->whereIn('status', [BookingStatus::Confirmed, BookingStatus::Pending])->count())
                ->icon('heroicon-o-arrow-left-end-on-rectangle'),
            Stat::make('إشغال الليلة', $totalUnits > 0 ? round($occupiedTonight / $totalUnits * 100).'%' : '—')
                ->description("{$occupiedTonight} من {$totalUnits} غرفة")->icon('heroicon-o-key'),
            Stat::make('إيراد الشهر المؤكد', number_format($monthRevenue).' ر.س')
                ->color('success')->icon('heroicon-o-banknotes'),
        ];
    }
}
