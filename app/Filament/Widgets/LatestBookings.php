<?php

namespace App\Filament\Widgets;

use App\Enums\UserRole;
use App\Filament\Resources\Bookings\BookingResource;
use App\Models\Booking;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class LatestBookings extends TableWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'آخر طلبات الحجز';

    public static function canView(): bool
    {
        return auth()->user()?->hasRole(UserRole::Admin, UserRole::Reception) ?? false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn () => Booking::query()->with('roomType')->latest()->limit(8))
            ->paginated(false)
            ->columns([
                TextColumn::make('reference')->label('الرقم')->weight('bold'),
                TextColumn::make('guest_name')->label('الضيف')->description(fn (Booking $record) => $record->guest_phone),
                TextColumn::make('roomType.name_ar')->label('الغرفة'),
                TextColumn::make('check_in')->label('الوصول')->date('Y-m-d'),
                TextColumn::make('nights')->label('ليالي'),
                TextColumn::make('total')->label('الإجمالي')->money('SAR'),
                TextColumn::make('status')->label('الحالة')->badge(),
            ])
            ->recordActions([
                Action::make('open')->label('فتح')->url(fn (Booking $record) => BookingResource::getUrl('view', ['record' => $record])),
            ]);
    }
}
