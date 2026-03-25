<?php

namespace App\Filament\Resources\WaitlistEntries;

use App\Enums\UserRole;
use App\Filament\Resources\WaitlistEntries\Pages\ListWaitlistEntries;
use App\Filament\Resources\WaitlistEntries\Schemas\WaitlistEntryForm;
use App\Filament\Resources\WaitlistEntries\Tables\WaitlistEntriesTable;
use App\Filament\Traits\RequiresRole;
use App\Models\WaitlistEntry;
use App\Traits\HasPlanGating;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class WaitlistEntryResource extends Resource
{
    use HasPlanGating, RequiresRole;

    protected static function getRequiredRole(): UserRole
    {
        return UserRole::Manager;
    }

    protected static ?string $model = WaitlistEntry::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-queue-list';

    protected static string|\UnitEnum|null $navigationGroup = 'Shop';

    protected static ?int $navigationSort = 6;

    protected static ?string $navigationLabel = 'Waitlist';

    public static function form(Schema $schema): Schema
    {
        return WaitlistEntryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WaitlistEntriesTable::configure($table);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['customer_email', 'customer_name'];
    }

    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return $record->customer_name ?? $record->customer_email;
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Email' => $record->customer_email ?? 'N/A',
            'Product' => $record->product?->name ?? 'N/A',
        ];
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListWaitlistEntries::route('/'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) WaitlistEntry::query()->waiting()->count() ?: null;
    }
}
