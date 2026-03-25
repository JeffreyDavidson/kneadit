<?php

namespace App\Filament\Resources\Recipes;

use App\Enums\SubscriptionTier;
use App\Filament\Concerns\ShowsUpgradeBadge;
use App\Filament\Resources\Recipes\Pages\ListRecipes;
use App\Filament\Resources\Recipes\Schemas\RecipeForm;
use App\Filament\Resources\Recipes\Tables\RecipesTable;
use App\Models\Recipe;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Laravel\Pennant\Feature;

class RecipeResource extends Resource
{
    use ShowsUpgradeBadge;

    protected static ?string $model = Recipe::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-beaker';

    protected static string|\UnitEnum|null $navigationGroup = 'Tools';

    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return RecipeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RecipesTable::configure($table);
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
        return [
            //
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name'];
    }

    /** @param Recipe $record */
    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return $record->name;
    }

    /** @param Recipe $record */
    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Product' => $record->product->name ?? 'N/A',
            'Prep Time' => ($record->prep_time_minutes ?? 0).' min',
        ];
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with('product');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRecipes::route('/'),
        ];
    }
}
