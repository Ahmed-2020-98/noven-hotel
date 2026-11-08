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

class ClosuresRelationManager extends RelationManager
{
    protected static string $relationship = 'closures';

    protected static ?string $title = 'إغلاق وحدات (صيانة / حجز خارجي)';

    protected static ?string $modelLabel = 'إغلاق';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            DatePicker::make('starts_on')->label('من ليلة')->required()->native(false),
            DatePicker::make('ends_on')->label('حتى ليلة')->required()->native(false)->afterOrEqual('starts_on'),
            TextInput::make('units')->label('عدد الوحدات المغلقة')->numeric()->minValue(1)
                ->maxValue(fn () => $this->getOwnerRecord()->units_count)->default(1)->required(),
            TextInput::make('reason')->label('السبب')->placeholder('صيانة، حجز من Booking.com...'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->description('الوحدات المغلقة تُخصم من المتاح للحجز في هذه الليالي.')
            ->defaultSort('starts_on', 'desc')
            ->columns([
                TextColumn::make('starts_on')->label('من')->date('Y-m-d'),
                TextColumn::make('ends_on')->label('إلى')->date('Y-m-d'),
                TextColumn::make('units')->label('الوحدات')->badge(),
                TextColumn::make('reason')->label('السبب')->placeholder('—'),
            ])
            ->headerActions([CreateAction::make()])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }
}
