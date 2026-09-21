<?php

namespace App\Filament\Central\Resources;

use App\Filament\Central\Resources\SupportTicketResource\Tables\SupportTicketsTable;
use App\Models\Platform\SupportTicket;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class SupportTicketResource extends Resource
{
    #[\Override]
    protected static ?string $model = SupportTicket::class;

    #[\Override]
    protected static ?string $recordTitleAttribute = 'subject';

    #[\Override]
    public static function getGloballySearchableAttributes(): array
    {
        return ['subject'];
    }

    #[\Override]
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedInbox;

    #[\Override]
    protected static string|UnitEnum|null $navigationGroup = 'Platform';

    #[\Override]
    protected static ?int $navigationSort = 2;

    #[\Override]
    protected static ?string $navigationLabel = 'Support Inbox';

    public static function getNavigationBadge(): ?string
    {
        $count = rescue(
            fn (): int => cache()->remember(
                'filament.central.support_tickets.open_count',
                now()->addMinute(),
                fn (): int => SupportTicket::query()->open()->count(),
            ),
            fn (): int => SupportTicket::query()->open()->count(),
            report: false,
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
    #[\Override]
    public static function canCreate(): bool
    {
        return false;
    }

    #[\Override]
    public static function canEdit(Model $record): bool
    {
        return false;
    }

    #[\Override]
    public static function table(Table $table): Table
    {
        return SupportTicketsTable::configure($table);
    }

    #[\Override]
    public static function getRelations(): array
    {
        return [];
    }

    #[\Override]
    public static function getPages(): array
    {
        return [
            'index' => SupportTicketResource\Pages\ListTickets::route('/'),
            'view' => SupportTicketResource\Pages\ViewTicket::route('/{record}'),
        ];
    }
}
