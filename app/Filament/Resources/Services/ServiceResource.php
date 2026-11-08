<?php

namespace App\Filament\Resources\Services;

use App\Enums\UserRole;
use App\Filament\Concerns\RestrictedToRoles;
use App\Filament\Resources\Services\Pages\ManageServices;
use App\Filament\Support\IconOptions;
use App\Models\Service;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use UnitEnum;

class ServiceResource extends Resource
{
    use RestrictedToRoles;

    protected static ?string $model = Service::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSparkles;

    protected static string|UnitEnum|null $navigationGroup = 'المحتوى';

    protected static ?int $navigationSort = 10;

    protected static ?string $modelLabel = 'خدمة';

    protected static ?string $pluralModelLabel = 'خدمات الفندق';

    public static function allowedRoles(): array
    {
        return [UserRole::Admin, UserRole::Editor];
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('title_ar')->label('الاسم (عربي)')->required()->maxLength(255),
            TextInput::make('title_en')->label('Title (English)')->required()->maxLength(255),
            Textarea::make('description_ar')->label('الوصف (عربي)')->rows(3),
            Textarea::make('description_en')->label('Description (English)')->rows(3),
            Select::make('icon')->label('الأيقونة')->options(IconOptions::all())->default('sparkles')->required(),
            FileUpload::make('image')->label('صورة (اختياري)')->image()->disk('public')->directory('services')->maxSize(4096),
            TextInput::make('sort_order')->label('الترتيب')->numeric()->default(0),
            Toggle::make('is_active')->label('ظاهر في الموقع')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->columns([
                ImageColumn::make('image')->label('صورة')->disk('public'),
                TextColumn::make('title_ar')->label('الخدمة')->searchable(),
                TextColumn::make('title_en')->label('English')->toggleable(),
                ToggleColumn::make('is_active')->label('ظاهر'),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageServices::route('/')];
    }
}
