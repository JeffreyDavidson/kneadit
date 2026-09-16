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
    #[\Override]
    protected static ?string $model = WaitlistEntry::class;

    #[\Override]
    protected static ?string $recordTitleAttribute = 'email';

    #[\Override]
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedQueueList;

    #[\Override]
    protected static string|\UnitEnum|null $navigationGroup = 'Shop';

    #[\Override]
    protected static ?int $navigationSort = 6;

    #[\Override]
    protected static ?string $navigationLabel = 'Waitlist';

    #[\Override]
    public static function form(Schema $schema): Schema
    {
        return WaitlistEntryForm::configure($schema);
    }

    #[\Override]
    public static function table(Table $table): Table
    {
        return WaitlistEntriesTable::configure($table);
    }

    #[\Override]
    public static function getGloballySearchableAttributes(): array
    {
        return ['customer_email', 'customer_name'];
    }

    /** @param WaitlistEntry $record */
    #[\Override]
    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return $record->customer_name ?? $record->customer_email;
    }

    /** @param WaitlistEntry $record */
    #[\Override]
    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Email' => $record->customer_email ?? 'N/A',
            'Product' => $record->product->name ?? 'N/A',
        ];
    }

    #[\Override]
    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    #[\Override]
    public static function getPages(): array
    {
        return [
            'index' => ListWaitlistEntries::route('/'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $count = cache()->remember('navigation-badge:waitlist-entries:waiting', 60, fn (): int => WaitlistEntry::query()->waiting()->count());

        return $count > 0 ? (string) $count : null;
    }
}
