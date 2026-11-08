<?php

namespace App\Filament\Pages;

use App\Enums\UserRole;
use App\Filament\Concerns\RestrictedToRoles;
use App\Models\RoomType;
use App\Services\BookingService;
use BackedEnum;
use Carbon\CarbonImmutable;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

/**
 * Free units per room type per night — the front desk's availability grid.
 */
class OccupancyCalendar extends Page
{
    use RestrictedToRoles;

    public const DAYS = 14;

    protected string $view = 'filament.pages.occupancy-calendar';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTableCells;

    protected static string|UnitEnum|null $navigationGroup = 'الحجوزات';

    protected static ?int $navigationSort = 20;

    protected static ?string $navigationLabel = 'تقويم الإشغال';

    protected static ?string $title = 'تقويم الإشغال (الغرف المتاحة لكل ليلة)';

    public string $start = '';

    public static function allowedRoles(): array
    {
        return [UserRole::Admin, UserRole::Reception];
    }

    public function mount(): void
    {
        $this->start = today()->toDateString();
    }

    public function shift(int $days): void
    {
        $this->start = CarbonImmutable::parse($this->start)->addDays($days)->toDateString();
    }

    public function today(): void
    {
        $this->start = today()->toDateString();
    }

    /** @return array<string, mixed> */
    protected function getViewData(): array
    {
        $from = CarbonImmutable::parse($this->start ?: today());
        $roomTypes = RoomType::active()->get();

        return [
            'roomTypes' => $roomTypes,
            'days' => collect(range(0, self::DAYS - 1))->map(fn (int $i) => $from->addDays($i)),
            'matrix' => app(BookingService::class)->availabilityMatrix($roomTypes, $from, self::DAYS),
        ];
    }
}
