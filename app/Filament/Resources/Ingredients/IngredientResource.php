<?php

namespace App\Filament\Resources\Ingredients;

use App\Enums\SubscriptionTier;
use App\Filament\Concerns\ShowsUpgradeBadge;
use App\Filament\Resources\Ingredients\Pages\ListIngredients;
use App\Filament\Resources\Ingredients\Schemas\IngredientForm;
use App\Filament\Resources\Ingredients\Tables\IngredientsTable;
use App\Models\Ingredient;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Laravel\Pennant\Feature;

class IngredientResource extends Resource
{
    use ShowsUpgradeBadge;

    protected static ?string $model = Ingredient::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-beaker';

    protected static string|\UnitEnum|null $navigationGroup = 'Shop';

    protected static ?int $navigationSort = 10;

    protected static ?string $navigationLabel = 'Ingredients';

    public static function form(Schema $schema): Schema
    {
        return IngredientForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return IngredientsTable::configure($table);
    }

    public static function canAccess(): bool
    {
        return Feature::active('pro-features');
    }

    protected static function requiredTier(): SubscriptionTier
    {
        return SubscriptionTier::Pro;
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'supplier'];
    }

    /** @param Ingredient $record */
    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return $record->name;
    }

    /** @param Ingredient $record */
    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Supplier' => $record->supplier ?? 'N/A',
            'Stock' => $record->current_stock . ' ' . ($record->unit ?? ''),
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListIngredients::route('/'),
        ];
    }
}
