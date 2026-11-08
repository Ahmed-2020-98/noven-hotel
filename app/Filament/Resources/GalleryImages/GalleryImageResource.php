<?php

namespace App\Filament\Resources\GalleryImages;

use App\Enums\UserRole;
use App\Filament\Concerns\RestrictedToRoles;
use App\Filament\Resources\GalleryImages\Pages\ManageGalleryImages;
use App\Models\GalleryImage;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class GalleryImageResource extends Resource
{
    use RestrictedToRoles;

    public const CATEGORIES = [
        'hotel' => 'الفندق',
        'rooms' => 'الغرف',
        'dining' => 'المطعم والقهوة',
        'facilities' => 'المرافق',
    ];

    protected static ?string $model = GalleryImage::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    protected static string|UnitEnum|null $navigationGroup = 'المحتوى';

    protected static ?int $navigationSort = 20;

    protected static ?string $modelLabel = 'صورة';

    protected static ?string $pluralModelLabel = 'معرض الصور';

    public static function allowedRoles(): array
    {
        return [UserRole::Admin, UserRole::Editor];
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            FileUpload::make('image')->label('الصورة')->image()->required()->disk('public')->directory('gallery')->maxSize(6144)->columnSpanFull(),
            TextInput::make('caption_ar')->label('وصف (عربي)'),
            TextInput::make('caption_en')->label('Caption (English)'),
            Select::make('category')->label('التصنيف')->options(self::CATEGORIES)->default('hotel')->required(),
            TextInput::make('sort_order')->label('الترتيب')->numeric()->default(0),
            Toggle::make('is_active')->label('ظاهرة في الموقع')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->columns([
                ImageColumn::make('image')->label('الصورة')->disk('public')->imageHeight(64),
                TextColumn::make('caption_ar')->label('الوصف'),
                TextColumn::make('category')->label('التصنيف')->formatStateUsing(fn (string $state) => self::CATEGORIES[$state] ?? $state)->badge(),
                ToggleColumn::make('is_active')->label('ظاهرة'),
            ])
            ->filters([SelectFilter::make('category')->label('التصنيف')->options(self::CATEGORIES)])
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageGalleryImages::route('/')];
    }
}
