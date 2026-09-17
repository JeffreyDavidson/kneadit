<?php

namespace App\Filament\Resources\Ingredients;

use App\Enums\Platform\SubscriptionTier;
use App\Filament\Concerns\ShowsUpgradeBadge;
use App\Filament\Resources\Ingredients\Pages\ListIngredients;
use App\Filament\Resources\Ingredients\Schemas\IngredientForm;
use App\Filament\Resources\Ingredients\Tables\IngredientsTable;
use App\Models\Inventory\Ingredient;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Laravel\Pennant\Feature;

class IngredientResource extends Resource
{
    use ShowsUpgradeBadge;

    #[\Override]
    protected static ?string $model = Ingredient::class;

    #[\Override]
    protected static ?string $recordTitleAttribute = 'name';

    #[\Override]
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedBeaker;

    #[\Override]
    protected static string|\UnitEnum|null $navigationGroup = 'Shop';

    #[\Override]
    protected static ?int $navigationSort = 10;

    #[\Override]
    protected static ?string $navigationLabel = 'Ingredients';

    #[\Override]
    public static function form(Schema $schema): Schema
    {
        return IngredientForm::configure($schema);
    }

    #[\Override]
    public static function table(Table $table): Table
    {
        return IngredientsTable::configure($table);
    }

    #[\Override]
    public static function canAccess(): bool
    {
        return Feature::active('pro-features');
    }

    protected static function requiredTier(): SubscriptionTier
    {
        return SubscriptionTier::Pro;
    }

    #[\Override]
    public static function getRelations(): array
    {
        return [];
    }

    #[\Override]
    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'supplier'];
    }

    /** @param Ingredient $record */
    #[\Override]
    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return $record->name;
    }

    /** @param Ingredient $record */
    #[\Override]
    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Supplier' => $record->supplier ?? 'N/A',
            'Stock' => $record->current_stock.' '.($record->unit ?? ''),
        ];
    }

    #[\Override]
    public static function getPages(): array
    {
        return [
            'index' => ListIngredients::route('/'),
        ];
    }
}
