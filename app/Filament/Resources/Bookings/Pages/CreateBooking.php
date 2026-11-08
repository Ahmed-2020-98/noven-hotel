<?php

namespace App\Filament\Resources\Bookings\Pages;

use App\Enums\BookingSource;
use App\Enums\BookingStatus;
use App\Exceptions\RoomUnavailableException;
use App\Filament\Resources\Bookings\BookingResource;
use App\Services\BookingService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateBooking extends CreateRecord
{
    protected static string $resource = BookingResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        try {
            return app(BookingService::class)->create(
                [...$data, 'locale' => 'ar'],
                BookingSource::from($data['source'] instanceof BookingSource ? $data['source']->value : $data['source']),
                BookingStatus::from($data['status']),
            );
        } catch (RoomUnavailableException $e) {
            Notification::make()->title('لا توجد وحدات متاحة')
                ->body("المتاح في هذه الفترة: {$e->availableUnits} غرفة.")->danger()->send();
            $this->halt();
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }
}
