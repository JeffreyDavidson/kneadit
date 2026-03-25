<?php

namespace App\Filament\Resources\GiftCards;

use App\Enums\SubscriptionTier;
use App\Filament\Concerns\ShowsUpgradeBadge;
use App\Filament\Resources\GiftCards\Pages\ListGiftCards;
use App\Filament\Resources\GiftCards\Pages\ViewGiftCard;
use App\Filament\Resources\GiftCards\Schemas\GiftCardForm;
use App\Filament\Resources\GiftCards\Tables\GiftCardsTable;
use App\Models\GiftCard;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Laravel\Pennant\Feature;

class GiftCardResource extends Resource
{
    use ShowsUpgradeBadge;

    protected static ?string $model = GiftCard::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-gift-top';

    protected static string|\UnitEnum|null $navigationGroup = 'Shop';

    protected static ?int $navigationSort = 11;

    protected static ?string $navigationLabel = 'Gift Cards';

    protected static ?string $pluralModelLabel = 'Gift Cards';

    public static function form(Schema $schema): Schema
    {
        return GiftCardForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return GiftCardsTable::configure($table);
    }

    public static function canAccess(): bool
    {
        return Feature::active('growth-features');
    }

    protected static function requiredTier(): SubscriptionTier
    {
        return SubscriptionTier::Growth;
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['code', 'purchaser_name', 'recipient_name'];
    }

    /** @param GiftCard $record */
    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return 'Gift Card: '.$record->code;
    }

    /** @param GiftCard $record */
    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Balance' => '$'.number_format($record->current_balance, 2),
            'Recipient' => $record->recipient_name ?? 'N/A',
            'Active' => $record->is_active ? 'Yes' : 'No',
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListGiftCards::route('/'),
            'view' => ViewGiftCard::route('/{record}'),
        ];
    }
}
