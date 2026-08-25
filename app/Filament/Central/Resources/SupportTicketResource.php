<?php

namespace App\Filament\Central\Resources;

use App\Filament\Central\Resources\SupportTicketResource\Tables\SupportTicketsTable;
use App\Models\Platform\SupportTicket;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Cache;
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

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Support Inbox';

    public static function getNavigationBadge(): ?string
    {
        $count = Cache::remember(
            'filament.central.support_tickets.open_count',
            now()->addMinute(),
            fn (): int => SupportTicket::query()->open()->count(),
        );

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'danger';
    }

    /**
     * Tickets originate from bakers; platform admins only view/reply/resolve
     * via ViewTicket. No create or edit surface.
     */
    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return false;
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
