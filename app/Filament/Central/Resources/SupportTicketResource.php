<?php

namespace App\Filament\Central\Resources;

use App\Filament\Central\Resources\SupportTicketResource\Schemas\SupportTicketForm;
use App\Filament\Central\Resources\SupportTicketResource\Tables\SupportTicketsTable;
use App\Models\Platform\SupportTicket;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class SupportTicketResource extends Resource
{
    protected static ?string $model = SupportTicket::class;

    protected static ?string $recordTitleAttribute = 'subject';

    public static function getGloballySearchableAttributes(): array
    {
        return ['subject'];
    }

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedInbox;

    protected static string|UnitEnum|null $navigationGroup = 'Platform';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Support Inbox';

    public static function getNavigationBadge(): ?string
    {
        $count = SupportTicket::query()->open()->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'danger';
    }

    public static function form(Schema $form): Schema
    {
        return SupportTicketForm::configure($form);
    }

    public static function table(Table $table): Table
    {
        return SupportTicketsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => SupportTicketResource\Pages\ListTickets::route('/'),
            'view' => SupportTicketResource\Pages\ViewTicket::route('/{record}'),
        ];
    }
}
