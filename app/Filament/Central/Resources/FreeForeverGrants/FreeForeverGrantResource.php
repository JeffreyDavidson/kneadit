<?php

declare(strict_types=1);

namespace App\Filament\Central\Resources\FreeForeverGrants;

use App\Filament\Central\Resources\FreeForeverGrants\Pages\ListFreeForeverGrants;
use App\Filament\Central\Resources\FreeForeverGrants\Tables\FreeForeverGrantsTable;
use App\Models\Platform\FreeForeverGrant;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class FreeForeverGrantResource extends Resource
{
    #[\Override]
    protected static ?string $model = FreeForeverGrant::class;

    #[\Override]
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedGift;

    #[\Override]
    protected static string|UnitEnum|null $navigationGroup = 'Settings';

    #[\Override]
    protected static ?string $navigationLabel = 'Free Forever Grants';

    #[\Override]
    protected static ?int $navigationSort = 30;

    /**
     * Grants are created from the Tenants list bulk action; this resource
     * is the audit ledger surface only.
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
        return FreeForeverGrantsTable::configure($table);
    }

    #[\Override]
    public static function getPages(): array
    {
        return [
            'index' => ListFreeForeverGrants::route('/'),
        ];
    }
}
