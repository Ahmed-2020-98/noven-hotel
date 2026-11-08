<?php

namespace App\Filament\Resources\RoomTypes;

use App\Enums\UserRole;
use App\Filament\Concerns\RestrictedToRoles;
use App\Filament\Resources\RoomTypes\Pages\CreateRoomType;
use App\Filament\Resources\RoomTypes\Pages\EditRoomType;
use App\Filament\Resources\RoomTypes\Pages\ListRoomTypes;
use App\Filament\Resources\RoomTypes\RelationManagers\ClosuresRelationManager;
use App\Filament\Resources\RoomTypes\RelationManagers\SeasonsRelationManager;
use App\Models\Hotel;
use App\Models\RoomType;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use UnitEnum;

class RoomTypeResource extends Resource
{
    use RestrictedToRoles;

    protected static ?string $model = RoomType::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedKey;

    protected static string|UnitEnum|null $navigationGroup = 'الغرف';

    protected static ?int $navigationSort = 10;

    protected static ?string $modelLabel = 'نوع غرفة';

    protected static ?string $pluralModelLabel = 'أنواع الغرف والأسعار';

    protected static ?string $recordTitleAttribute = 'name_ar';

    public static function allowedRoles(): array
    {
        return [UserRole::Admin, UserRole::Editor, UserRole::Reception];
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->hasRole(UserRole::Admin, UserRole::Editor) ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make()->columnSpanFull()->persistTabInQueryString()->tabs([
                Tab::make('البيانات')->icon(Heroicon::OutlinedInformationCircle)->columns(2)->schema([
                    Select::make('hotel_id')->label('الفرع')->relationship('hotel', 'name_ar')
                        ->default(fn () => Hotel::current()?->id)->required()
                        ->hidden(fn () => Hotel::count() < 2)->dehydratedWhenHidden(),
                    TextInput::make('name_ar')->label('الاسم (عربي)')->required(),
                    TextInput::make('name_en')->label('Name (English)')->required()->live(onBlur: true)
                        ->afterStateUpdated(function (?string $state, Set $set, string $operation) {
                            if ($operation === 'create') {
                                $set('slug', Str::slug((string) $state));
                            }
                        }),
                    TextInput::make('slug')->label('رابط الصفحة')->required()->alphaDash()->unique(ignoreRecord: true)
                        ->prefix('/rooms/'),
                    TextInput::make('summary_ar')->label('وصف مختصر (عربي)')->maxLength(300),
                    TextInput::make('summary_en')->label('Short summary (English)')->maxLength(300),
                    Textarea::make('description_ar')->label('الوصف الكامل (عربي)')->rows(5),
                    Textarea::make('description_en')->label('Full description (English)')->rows(5),
                    TextInput::make('beds_ar')->label('الأسرّة (عربي)')->placeholder('سرير كينج'),
                    TextInput::make('beds_en')->label('Beds (English)')->placeholder('King bed'),
                    TextInput::make('size_sqm')->label('المساحة')->numeric()->suffix('م²'),
                    Toggle::make('is_active')->label('ظاهرة ومتاحة للحجز')->default(true)->inline(false),
                    TextInput::make('max_adults')->label('أقصى عدد بالغين')->numeric()->minValue(1)->default(2)->required(),
                    TextInput::make('max_children')->label('أقصى عدد أطفال')->numeric()->minValue(0)->default(0)->required(),
                    CheckboxList::make('amenities')->label('مميزات الغرفة')->relationship('amenities', 'name_ar')
                        ->columns(3)->columnSpanFull(),
                ]),
                Tab::make('الأسعار والتوافر')->icon(Heroicon::OutlinedCurrencyDollar)->columns(3)->schema([
                    TextInput::make('base_price')->label('سعر الليلة')->numeric()->minValue(0)->suffix('ر.س')->required()
                        ->helperText('قبل الضريبة'),
                    TextInput::make('weekend_price')->label('سعر ليلة الويكند')->numeric()->minValue(0)->suffix('ر.س')
                        ->helperText('ليالي الخميس والجمعة — اتركه فارغًا لنفس السعر'),
                    TextInput::make('units_count')->label('عدد الوحدات')->numeric()->minValue(0)->required()->default(1)
                        ->helperText('كم غرفة من هذا النوع في الفندق'),
                    TextInput::make('sort_order')->label('ترتيب الظهور')->numeric()->default(0),
                ]),
                Tab::make('الصور')->icon(Heroicon::OutlinedPhoto)->schema([
                    FileUpload::make('cover_image')->label('الصورة الرئيسية')->image()->disk('public')
                        ->directory('rooms')->maxSize(6144)->imageEditor(),
                    FileUpload::make('gallery')->label('صور إضافية')->image()->multiple()->reorderable()->disk('public')
                        ->directory('rooms')->maxSize(6144)->maxFiles(12)->panelLayout('grid'),
                ]),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->columns([
                ImageColumn::make('cover_image')->label('')->disk('public')->imageHeight(48),
                TextColumn::make('name_ar')->label('نوع الغرفة')->searchable()->description(fn (RoomType $record) => $record->name_en),
                TextColumn::make('base_price')->label('سعر الليلة')->money('SAR')->sortable(),
                TextColumn::make('weekend_price')->label('الويكند')->money('SAR')->placeholder('—'),
                TextColumn::make('units_count')->label('الوحدات')->badge(),
                TextColumn::make('seasons_count')->label('مواسم')->counts('seasons')->toggleable(),
                ToggleColumn::make('is_active')->label('متاحة')
                    ->disabled(fn () => ! auth()->user()?->hasRole(UserRole::Admin, UserRole::Editor)),
            ])
            ->recordActions([EditAction::make()]);
    }

    public static function getRelations(): array
    {
        return [SeasonsRelationManager::class, ClosuresRelationManager::class];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRoomTypes::route('/'),
            'create' => CreateRoomType::route('/create'),
            'edit' => EditRoomType::route('/{record}/edit'),
        ];
    }
}
