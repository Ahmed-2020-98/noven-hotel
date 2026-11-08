<?php

namespace App\Filament\Resources\Bookings\Pages;

use App\Enums\BookingStatus;
use App\Filament\Resources\Bookings\BookingResource;
use App\Models\Booking;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ListBookings extends ListRecords
{
    protected static string $resource = BookingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export')->label('تصدير Excel')->icon(Heroicon::OutlinedArrowDownTray)->color('gray')
                ->action(fn () => $this->exportCsv()),
            CreateAction::make()->label('حجز جديد (هاتف / حضور)'),
        ];
    }

    public function getTabs(): array
    {
        $today = today()->toDateString();

        return [
            'all' => Tab::make('الكل'),
            'pending' => Tab::make('طلبات جديدة')
                ->badge(Booking::where('status', BookingStatus::Pending)->count() ?: null)->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $q) => $q->where('status', BookingStatus::Pending)),
            'arrivals' => Tab::make('وصول اليوم')
                ->modifyQueryUsing(fn (Builder $q) => $q->whereDate('check_in', $today)
                    ->whereIn('status', [BookingStatus::Confirmed, BookingStatus::Pending])),
            'in_house' => Tab::make('داخل الفندق')
                ->modifyQueryUsing(fn (Builder $q) => $q->where('status', BookingStatus::CheckedIn)),
            'departures' => Tab::make('مغادرة اليوم')
                ->modifyQueryUsing(fn (Builder $q) => $q->whereDate('check_out', $today)->where('status', BookingStatus::CheckedIn)),
            'upcoming' => Tab::make('القادمة')
                ->modifyQueryUsing(fn (Builder $q) => $q->where('check_in', '>', $today)->where('status', BookingStatus::Confirmed)),
        ];
    }

    /** CSV with a UTF-8 BOM so Excel shows Arabic correctly. */
    private function exportCsv(): StreamedResponse
    {
        $query = $this->getFilteredSortedTableQuery()->with('roomType');

        return response()->streamDownload(function () use ($query) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['الرقم', 'الحالة', 'المصدر', 'الضيف', 'الجوال', 'البريد', 'الغرفة', 'عدد الغرف', 'الوصول', 'المغادرة', 'الليالي', 'البالغين', 'الأطفال', 'المجموع', 'الضريبة', 'الإجمالي', 'تاريخ الطلب']);
            $query->chunk(500, function ($bookings) use ($out) {
                foreach ($bookings as $b) {
                    fputcsv($out, [
                        $b->reference, $b->status->getLabel(), $b->source->getLabel(), $b->guest_name, $b->guest_phone,
                        $b->guest_email, $b->roomType?->name_ar, $b->rooms, $b->check_in->toDateString(),
                        $b->check_out->toDateString(), $b->nights, $b->adults, $b->children, $b->subtotal,
                        $b->vat_amount, $b->total, $b->created_at->format('Y-m-d H:i'),
                    ]);
                }
            });
            fclose($out);
        }, 'bookings-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
