<?php

namespace App\Filament\Resources\GiftCards;

use App\Filament\Resources\GiftCards\Pages\CreateGiftCard;
use App\Filament\Resources\GiftCards\Pages\EditGiftCard;
use App\Filament\Resources\GiftCards\Pages\ListGiftCards;
use App\Filament\Resources\GiftCards\Pages\ViewGiftCard;
use App\Filament\Resources\GiftCards\Schemas\GiftCardForm;
use App\Filament\Resources\GiftCards\Tables\GiftCardsTable;
use App\Models\GiftCard;
use App\Traits\HasPlanGating;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class GiftCardResource extends Resource
{
    use HasPlanGating;

    protected static ?string $model = GiftCard::class;
    protected static string $requiredPlan = 'growth';

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

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListGiftCards::route('/'),
            'create' => CreateGiftCard::route('/create'),
            'view' => ViewGiftCard::route('/{record}'),
            'edit' => EditGiftCard::route('/{record}/edit'),
        ];
    }
}
