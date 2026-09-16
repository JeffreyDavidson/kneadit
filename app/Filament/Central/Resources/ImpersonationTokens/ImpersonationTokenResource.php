<?php

namespace App\Filament\Central\Resources\ImpersonationTokens;

use App\Filament\Central\Resources\ImpersonationTokens\Pages\ListImpersonationTokens;
use App\Filament\Central\Resources\ImpersonationTokens\Tables\ImpersonationTokensTable;
use App\Models\Platform\ImpersonationToken;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class ImpersonationTokenResource extends Resource
{
    #[\Override]
    protected static ?string $model = ImpersonationToken::class;

    #[\Override]
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFingerPrint;

    #[\Override]
    protected static string|UnitEnum|null $navigationGroup = 'Settings';

    #[\Override]
    protected static ?string $navigationLabel = 'Impersonation Log';

    #[\Override]
    protected static ?string $modelLabel = 'Impersonation';

    #[\Override]
    protected static ?int $navigationSort = 40;

    /**
     * Audit-only resource: tokens are minted by the "Login as Baker" action
     * on the Tenant view page. No create/edit surface.
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
    public static function canDelete(Model $record): bool
    {
        return false;
    }

    #[\Override]
    public static function table(Table $table): Table
    {
        return ImpersonationTokensTable::configure($table);
    }

    #[\Override]
    public static function getPages(): array
    {
        return [
            'index' => ListImpersonationTokens::route('/'),
        ];
    }
}
