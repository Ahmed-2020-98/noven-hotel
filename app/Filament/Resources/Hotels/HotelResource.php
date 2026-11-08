<?php

namespace App\Filament\Resources\Hotels;

use App\Enums\UserRole;
use App\Filament\Concerns\RestrictedToRoles;
use App\Filament\Resources\Hotels\Pages\ManageHotels;
use App\Models\Hotel;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class HotelResource extends Resource
{
    use RestrictedToRoles;

    protected static ?string $model = Hotel::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;

    protected static string|UnitEnum|null $navigationGroup = 'الإدارة';

    protected static ?int $navigationSort = 10;

    protected static ?string $modelLabel = 'فرع';

    protected static ?string $pluralModelLabel = 'الفندق والفروع';

    public static function allowedRoles(): array
    {
        return [UserRole::Admin];
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('البيانات الأساسية')->columns(2)->schema([
                TextInput::make('name_ar')->label('الاسم (عربي)')->required(),
                TextInput::make('name_en')->label('Name (English)')->required(),
                TextInput::make('slug')->label('المعرف في الرابط')->required()->alphaDash()->unique(ignoreRecord: true),
                Toggle::make('is_active')->label('مفعل')->default(true)->inline(false),
                TextInput::make('city_ar')->label('المدينة (عربي)'),
                TextInput::make('city_en')->label('City (English)'),
                TextInput::make('address_ar')->label('العنوان (عربي)'),
                TextInput::make('address_en')->label('Address (English)'),
            ]),
            Section::make('التواصل')->columns(3)->schema([
                TextInput::make('phone')->label('الهاتف')->tel(),
                TextInput::make('whatsapp')->label('واتساب')->tel()->helperText('بالصيغة الدولية مثل +9665xxxxxxxx'),
                TextInput::make('email')->label('البريد')->email(),
            ]),
            Section::make('الخريطة ومواعيد الإقامة')->columns(2)->schema([
                Textarea::make('map_embed_url')->label('رابط تضمين خريطة جوجل (Embed)')->rows(2)->columnSpanFull()
                    ->helperText('من خرائط جوجل: مشاركة ← تضمين خريطة ← انسخ قيمة src فقط.'),
                TextInput::make('map_link')->label('رابط الاتجاهات')->url(),
                TextInput::make('latitude')->label('خط العرض')->numeric(),
                TextInput::make('longitude')->label('خط الطول')->numeric(),
                TimePicker::make('check_in_time')->label('وقت تسجيل الدخول')->seconds(false),
                TimePicker::make('check_out_time')->label('وقت تسجيل الخروج')->seconds(false),
            ]),
        ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name_ar')->label('الاسم'),
                TextColumn::make('city_ar')->label('المدينة'),
                TextColumn::make('phone')->label('الهاتف'),
                TextColumn::make('room_types_count')->label('أنواع الغرف')->counts('roomTypes'),
            ])
            ->recordActions([EditAction::make()->slideOver()]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageHotels::route('/')];
    }
}
