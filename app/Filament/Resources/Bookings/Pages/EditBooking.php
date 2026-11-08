<?php

namespace App\Filament\Resources\Bookings\Pages;

use App\Exceptions\RoomUnavailableException;
use App\Filament\Resources\Bookings\BookingResource;
use App\Models\Booking;
use App\Services\BookingService;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditBooking extends EditRecord
{
    protected static string $resource = BookingResource::class;

    protected function getHeaderActions(): array
    {
        return [ViewAction::make()];
    }

    /** @param Booking $record */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        try {
            return app(BookingService::class)->update($record, $data);
        } catch (RoomUnavailableException $e) {
            Notification::make()->title('لا توجد وحدات متاحة للتعديل')
                ->body("المتاح في هذه الفترة: {$e->availableUnits} غرفة.")->danger()->send();
            $this->halt();
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }
}
