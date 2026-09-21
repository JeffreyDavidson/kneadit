<?php

namespace App\Filament\Resources\GiftCards;

use App\Enums\Platform\SubscriptionTier;
use App\Filament\Concerns\ShowsUpgradeBadge;
use App\Filament\Resources\GiftCards\Pages\ListGiftCards;
use App\Filament\Resources\GiftCards\Pages\ViewGiftCard;
use App\Filament\Resources\GiftCards\Schemas\GiftCardForm;
use App\Filament\Resources\GiftCards\Tables\GiftCardsTable;
use App\Models\Financial\GiftCard;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Laravel\Pennant\Feature;

class GiftCardResource extends Resource
{
    use ShowsUpgradeBadge;

    #[\Override]
    protected static ?string $model = GiftCard::class;

    #[\Override]
    protected static ?string $recordTitleAttribute = 'code';

    #[\Override]
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedGiftTop;

    #[\Override]
    protected static string|\UnitEnum|null $navigationGroup = 'Shop';

    #[\Override]
    protected static ?int $navigationSort = 11;

    #[\Override]
    protected static ?string $navigationLabel = 'Gift Cards';

    #[\Override]
    protected static ?string $pluralModelLabel = 'Gift Cards';

    #[\Override]
    public static function form(Schema $schema): Schema
    {
        return GiftCardForm::configure($schema);
    }

    #[\Override]
    public static function table(Table $table): Table
    {
        return GiftCardsTable::configure($table);
    }

    #[\Override]
    public static function canAccess(): bool
    {
        return Feature::active('growth-features');
    }

    protected static function requiredTier(): SubscriptionTier
    {
        return SubscriptionTier::Growth;
    }

    #[\Override]
    public static function getRelations(): array
    {
        return [];
    }

    #[\Override]
    public static function getGloballySearchableAttributes(): array
    {
        return ['code', 'purchaser_name', 'recipient_name'];
    }

    /** @param GiftCard $record */
    #[\Override]
    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return 'Gift Card: '.$record->code;
    }

    /** @param GiftCard $record */
    #[\Override]
    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Balance' => $record->current_balance->formatted(),
            'Recipient' => $record->recipient_name ?? 'N/A',
            'Active' => $record->is_active ? 'Yes' : 'No',
        ];
    }

    #[\Override]
    public static function getPages(): array
    {
        return [
            'index' => ListGiftCards::route('/'),
            'view' => ViewGiftCard::route('/{record}'),
        ];
    }
}
