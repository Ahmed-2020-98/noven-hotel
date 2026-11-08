<?php

namespace App\Filament\Resources\RoomTypes\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SeasonsRelationManager extends RelationManager
{
    protected static string $relationship = 'seasons';

    protected static ?string $title = 'أسعار المواسم';

    protected static ?string $modelLabel = 'موسم';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->label('اسم الموسم')->placeholder('رمضان، اليوم الوطني، موسم الرياض...')->required()->columnSpanFull(),
            DatePicker::make('starts_on')->label('من ليلة')->required()->native(false),
            DatePicker::make('ends_on')->label('حتى ليلة')->required()->native(false)->afterOrEqual('starts_on'),
            TextInput::make('price')->label('سعر الليلة')->numeric()->minValue(0)->suffix('ر.س')->required(),
            TextInput::make('weekend_price')->label('سعر الويكند')->numeric()->minValue(0)->suffix('ر.س'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->description('السعر الموسمي يطغى على السعر الأساسي في الليالي التي يغطيها.')
            ->defaultSort('starts_on', 'desc')
            ->columns([
                TextColumn::make('name')->label('الموسم'),
                TextColumn::make('starts_on')->label('من')->date('Y-m-d'),
                TextColumn::make('ends_on')->label('إلى')->date('Y-m-d'),
                TextColumn::make('price')->label('السعر')->money('SAR'),
                TextColumn::make('weekend_price')->label('الويكند')->money('SAR')->placeholder('—'),
            ])
            ->headerActions([CreateAction::make()])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }
}
