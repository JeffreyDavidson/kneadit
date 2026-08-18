<?php

namespace App\Filament\Resources\WaitlistEntries;

use App\Filament\Resources\WaitlistEntries\Pages\ListWaitlistEntries;
use App\Filament\Resources\WaitlistEntries\Schemas\WaitlistEntryForm;
use App\Filament\Resources\WaitlistEntries\Tables\WaitlistEntriesTable;
use App\Models\Customers\WaitlistEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class WaitlistEntryResource extends Resource
{
    protected static ?string $model = WaitlistEntry::class;

    protected static ?string $recordTitleAttribute = 'email';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedQueueList;

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

    /** @param WaitlistEntry $record */
    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return $record->customer_name ?? $record->customer_email;
    }

    /** @param WaitlistEntry $record */
    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Email' => $record->customer_email ?? 'N/A',
            'Product' => $record->product->name ?? 'N/A',
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
