<?php

namespace App\Filament\Resources\Bookings;

use App\Enums\BookingSource;
use App\Enums\BookingStatus;
use App\Enums\UserRole;
use App\Filament\Concerns\RestrictedToRoles;
use App\Filament\Resources\Bookings\Pages\CreateBooking;
use App\Filament\Resources\Bookings\Pages\EditBooking;
use App\Filament\Resources\Bookings\Pages\ListBookings;
use App\Filament\Resources\Bookings\Pages\ViewBooking;
use App\Models\Booking;
use App\Models\RoomType;
use App\Services\BookingNotifier;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class BookingResource extends Resource
{
    use RestrictedToRoles;

    protected static ?string $model = Booking::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static string|UnitEnum|null $navigationGroup = 'الحجوزات';

    protected static ?int $navigationSort = 10;

    protected static ?string $modelLabel = 'حجز';

    protected static ?string $pluralModelLabel = 'الحجوزات';

    protected static ?string $recordTitleAttribute = 'reference';

    public static function allowedRoles(): array
    {
        return [UserRole::Admin, UserRole::Reception];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['reference', 'guest_name', 'guest_phone'];
    }

    public static function getNavigationBadge(): ?string
    {
        $count = Booking::where('status', BookingStatus::Pending)->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): string
    {
        return 'warning';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('الإقامة')->columns(3)->schema([
                Select::make('room_type_id')->label('نوع الغرفة')->required()->columnSpan(2)
                    ->options(fn () => RoomType::orderBy('sort_order')->pluck('name_ar', 'id')),
                TextInput::make('rooms')->label('عدد الغرف')->numeric()->minValue(1)->default(1)->required(),
                DatePicker::make('check_in')->label('الوصول')->required()->native(false)->live(),
                DatePicker::make('check_out')->label('المغادرة')->required()->native(false)->after('check_in'),
                Select::make('source')->label('مصدر الحجز')->options(BookingSource::class)->default(BookingSource::Phone)->required(),
                TextInput::make('adults')->label('البالغين')->numeric()->minValue(1)->default(2)->required(),
                TextInput::make('children')->label('الأطفال')->numeric()->minValue(0)->default(0)->required(),
                Select::make('status')->label('الحالة')->required()->visibleOn('create')
                    ->options([BookingStatus::Confirmed->value => BookingStatus::Confirmed->getLabel(), BookingStatus::Pending->value => BookingStatus::Pending->getLabel()])
                    ->default(BookingStatus::Confirmed->value),
            ]),
            Section::make('الضيف')->columns(2)->schema([
                TextInput::make('guest_name')->label('الاسم')->required(),
                TextInput::make('guest_phone')->label('الجوال')->tel()->required(),
                TextInput::make('guest_email')->label('البريد')->email(),
                TextInput::make('guest_nationality')->label('الجنسية'),
                Textarea::make('notes')->label('ملاحظات الضيف')->rows(2),
                Textarea::make('admin_notes')->label('ملاحظات داخلية (لا تظهر للضيف)')->rows(2),
            ]),
        ])->columns(1);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(3)->columnSpanFull()->schema([
                Section::make('الحجز')->columnSpan(2)->columns(3)->schema([
                    TextEntry::make('reference')->label('رقم الطلب')->copyable()->weight('bold'),
                    TextEntry::make('status')->label('الحالة')->badge(),
                    TextEntry::make('source')->label('المصدر')->badge()->color('gray'),
                    TextEntry::make('roomType.name_ar')->label('نوع الغرفة'),
                    TextEntry::make('rooms')->label('عدد الغرف'),
                    TextEntry::make('nights')->label('الليالي'),
                    TextEntry::make('check_in')->label('الوصول')->date('l Y-m-d'),
                    TextEntry::make('check_out')->label('المغادرة')->date('l Y-m-d'),
                    TextEntry::make('guests')->label('الضيوف')
                        ->state(fn (Booking $record) => "{$record->adults} بالغ، {$record->children} طفل"),
                    TextEntry::make('expires_at')->label('محجوز مؤقتًا حتى')->dateTime('Y-m-d H:i')
                        ->visible(fn (Booking $record) => $record->status === BookingStatus::Pending),
                    TextEntry::make('created_at')->label('تاريخ الطلب')->dateTime('Y-m-d H:i'),
                ]),
                Section::make('السعر')->columnSpan(1)->schema([
                    TextEntry::make('subtotal')->label('المجموع')->money('SAR'),
                    TextEntry::make('vat_amount')->label(fn (Booking $record) => "الضريبة ({$record->vat_rate}%)")->money('SAR'),
                    TextEntry::make('total')->label('الإجمالي')->money('SAR')->weight('bold')->size('lg'),
                ]),
                Section::make('الضيف')->columnSpan(2)->columns(2)->schema([
                    TextEntry::make('guest_name')->label('الاسم'),
                    TextEntry::make('guest_phone')->label('الجوال')->url(fn (Booking $record) => 'tel:'.$record->guest_phone),
                    TextEntry::make('guest_email')->label('البريد')->placeholder('—'),
                    TextEntry::make('guest_nationality')->label('الجنسية')->placeholder('—'),
                    TextEntry::make('notes')->label('ملاحظات الضيف')->placeholder('—')->columnSpanFull(),
                    TextEntry::make('admin_notes')->label('ملاحظات داخلية')->placeholder('—')->columnSpanFull(),
                ]),
                Section::make('سجل الحالة')->columnSpan(1)->schema([
                    RepeatableEntry::make('statusLogs')->hiddenLabel()->contained(false)->schema([
                        TextEntry::make('to_status')->hiddenLabel()->badge()
                            ->belowContent(fn ($record) => $record->created_at->format('Y-m-d H:i').' — '.($record->user?->name ?? 'النظام')),
                    ]),
                ]),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('reference')->label('الرقم')->searchable()->copyable()->weight('bold'),
                TextColumn::make('guest_name')->label('الضيف')->searchable()->description(fn (Booking $record) => $record->guest_phone),
                TextColumn::make('guest_phone')->searchable()->hidden(),
                TextColumn::make('roomType.name_ar')->label('الغرفة')
                    ->description(fn (Booking $record) => $record->rooms > 1 ? "× {$record->rooms}" : null),
                TextColumn::make('check_in')->label('الوصول')->date('Y-m-d')->sortable(),
                TextColumn::make('check_out')->label('المغادرة')->date('Y-m-d')->sortable(),
                TextColumn::make('nights')->label('ليالي')->alignCenter(),
                TextColumn::make('total')->label('الإجمالي')->money('SAR')->sortable(),
                TextColumn::make('status')->label('الحالة')->badge(),
                TextColumn::make('source')->label('المصدر')->badge()->color('gray')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')->label('أُنشئ')->since()->sortable()->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')->label('الحالة')->options(BookingStatus::class)->multiple(),
                SelectFilter::make('room_type_id')->label('نوع الغرفة')->relationship('roomType', 'name_ar'),
                SelectFilter::make('source')->label('المصدر')->options(BookingSource::class),
                Filter::make('stay')->label('فترة الإقامة')
                    ->schema([
                        DatePicker::make('from')->label('من')->native(false),
                        DatePicker::make('until')->label('إلى')->native(false),
                    ])
                    ->query(fn (Builder $query, array $data) => $query
                        ->when($data['from'] ?? null, fn (Builder $q, $date) => $q->where('check_out', '>', $date))
                        ->when($data['until'] ?? null, fn (Builder $q, $date) => $q->where('check_in', '<=', $date))),
            ])
            ->recordActions([
                ViewAction::make()->iconButton(),
                ActionGroup::make([
                    ...static::statusActions(),
                    static::whatsappAction(),
                    EditAction::make(),
                ]),
            ]);
    }

    /** @return list<Action> */
    public static function statusActions(): array
    {
        $meta = [
            BookingStatus::Confirmed->value => ['تأكيد الحجز', Heroicon::OutlinedCheckCircle, 'success'],
            BookingStatus::CheckedIn->value => ['تسجيل الوصول', Heroicon::OutlinedKey, 'info'],
            BookingStatus::CheckedOut->value => ['تسجيل المغادرة', Heroicon::OutlinedArrowRightStartOnRectangle, 'gray'],
            BookingStatus::Cancelled->value => ['إلغاء الحجز', Heroicon::OutlinedXCircle, 'danger'],
        ];

        return collect($meta)->map(function (array $m, string $status) {
            $target = BookingStatus::from($status);

            return Action::make('to_'.$status)
                ->label($m[0])->icon($m[1])->color($m[2])
                ->visible(fn (Booking $record) => in_array($target, $record->status->next(), true))
                ->requiresConfirmation()
                ->modalDescription(fn (Booking $record) => $target === BookingStatus::Confirmed && $record->guest_email
                    ? 'سيتم إرسال إيميل تأكيد للضيف.' : null)
                ->schema($target === BookingStatus::Cancelled ? [TextInput::make('note')->label('سبب الإلغاء')] : [])
                ->action(function (Booking $record, array $data) use ($target) {
                    $record->transitionTo($target, $data['note'] ?? null);
                    app(BookingNotifier::class)->statusChanged($record);
                    Notification::make()->title('تم تحديث الحالة: '.$target->getLabel())->success()->send();
                });
        })->values()->all();
    }

    public static function whatsappAction(): Action
    {
        return Action::make('whatsapp')
            ->label('واتساب الضيف')
            ->icon(Heroicon::OutlinedChatBubbleLeftRight)
            ->color('success')
            ->url(function (Booking $record) {
                $message = "مرحبًا {$record->guest_name}، معك فندق نوفين بخصوص طلب الحجز رقم {$record->reference} "
                    ."({$record->roomType->name_ar} من {$record->check_in->format('Y-m-d')} إلى {$record->check_out->format('Y-m-d')}).";

                return 'https://wa.me/'.$record->guestWhatsappNumber().'?text='.rawurlencode($message);
            }, shouldOpenInNewTab: true);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBookings::route('/'),
            'create' => CreateBooking::route('/create'),
            'view' => ViewBooking::route('/{record}'),
            'edit' => EditBooking::route('/{record}/edit'),
        ];
    }
}
