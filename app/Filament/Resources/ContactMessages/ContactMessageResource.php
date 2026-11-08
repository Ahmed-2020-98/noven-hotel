<?php

namespace App\Filament\Resources\ContactMessages;

use App\Enums\UserRole;
use App\Filament\Concerns\RestrictedToRoles;
use App\Filament\Resources\ContactMessages\Pages\ManageContactMessages;
use App\Models\ContactMessage;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use UnitEnum;

class ContactMessageResource extends Resource
{
    use RestrictedToRoles;

    protected static ?string $model = ContactMessage::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;

    protected static string|UnitEnum|null $navigationGroup = 'الحجوزات';

    protected static ?int $navigationSort = 30;

    protected static ?string $modelLabel = 'رسالة';

    protected static ?string $pluralModelLabel = 'رسائل التواصل';

    public static function allowedRoles(): array
    {
        return [UserRole::Admin, UserRole::Reception];
    }

    public static function getNavigationBadge(): ?string
    {
        $count = ContactMessage::where('is_read', false)->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('name')->label('الاسم'),
            TextEntry::make('created_at')->label('التاريخ')->dateTime('Y-m-d H:i'),
            TextEntry::make('phone')->label('الجوال')->url(fn (ContactMessage $record) => $record->phone ? 'tel:'.$record->phone : null),
            TextEntry::make('email')->label('البريد')->url(fn (ContactMessage $record) => $record->email ? 'mailto:'.$record->email : null),
            TextEntry::make('subject')->label('الموضوع')->columnSpanFull(),
            TextEntry::make('message')->label('الرسالة')->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                IconColumn::make('is_read')->label('مقروءة')->boolean(),
                TextColumn::make('name')->label('الاسم')->searchable()->weight(fn (ContactMessage $record) => $record->is_read ? null : 'bold'),
                TextColumn::make('phone')->label('الجوال')->searchable(),
                TextColumn::make('subject')->label('الموضوع')->limit(40),
                TextColumn::make('created_at')->label('التاريخ')->since()->sortable(),
            ])
            ->filters([TernaryFilter::make('is_read')->label('مقروءة')])
            ->recordActions([
                ViewAction::make()->after(fn (ContactMessage $record) => $record->update(['is_read' => true])),
                Action::make('markRead')->label('تمت القراءة')->icon(Heroicon::OutlinedCheck)
                    ->visible(fn (ContactMessage $record) => ! $record->is_read)
                    ->action(fn (ContactMessage $record) => $record->update(['is_read' => true])),
                DeleteAction::make(),
            ])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageContactMessages::route('/')];
    }
}
