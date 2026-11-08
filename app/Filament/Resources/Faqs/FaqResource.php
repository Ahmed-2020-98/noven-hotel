<?php

namespace App\Filament\Resources\Faqs;

use App\Enums\UserRole;
use App\Filament\Concerns\RestrictedToRoles;
use App\Filament\Resources\Faqs\Pages\ManageFaqs;
use App\Models\Faq;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use UnitEnum;

class FaqResource extends Resource
{
    use RestrictedToRoles;

    protected static ?string $model = Faq::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedQuestionMarkCircle;

    protected static string|UnitEnum|null $navigationGroup = 'المحتوى';

    protected static ?int $navigationSort = 30;

    protected static ?string $modelLabel = 'سؤال';

    protected static ?string $pluralModelLabel = 'الأسئلة الشائعة';

    public static function allowedRoles(): array
    {
        return [UserRole::Admin, UserRole::Editor];
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('question_ar')->label('السؤال (عربي)')->required()->maxLength(255),
            TextInput::make('question_en')->label('Question (English)')->required()->maxLength(255),
            Textarea::make('answer_ar')->label('الإجابة (عربي)')->required()->rows(3),
            Textarea::make('answer_en')->label('Answer (English)')->required()->rows(3),
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
                TextColumn::make('question_ar')->label('السؤال')->searchable()->wrap(),
                ToggleColumn::make('is_active')->label('ظاهر'),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageFaqs::route('/')];
    }
}
