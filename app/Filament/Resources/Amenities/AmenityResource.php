<?php

namespace App\Filament\Resources\Amenities;

use App\Enums\UserRole;
use App\Filament\Concerns\RestrictedToRoles;
use App\Filament\Resources\Amenities\Pages\ManageAmenities;
use App\Filament\Support\IconOptions;
use App\Models\Amenity;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class AmenityResource extends Resource
{
    use RestrictedToRoles;

    protected static ?string $model = Amenity::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedStar;

    protected static string|UnitEnum|null $navigationGroup = 'الغرف';

    protected static ?int $navigationSort = 20;

    protected static ?string $modelLabel = 'ميزة';

    protected static ?string $pluralModelLabel = 'مميزات الغرف';

    public static function allowedRoles(): array
    {
        return [UserRole::Admin, UserRole::Editor];
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name_ar')->label('الاسم (عربي)')->required(),
            TextInput::make('name_en')->label('Name (English)')->required(),
            Select::make('icon')->label('الأيقونة')->options(IconOptions::all())->default('check-circle')->required(),
            TextInput::make('sort_order')->label('الترتيب')->numeric()->default(0),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->columns([
                TextColumn::make('name_ar')->label('الميزة')->searchable(),
                TextColumn::make('name_en')->label('English'),
                TextColumn::make('room_types_count')->label('عدد الغرف')->counts('roomTypes'),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageAmenities::route('/')];
    }
}
