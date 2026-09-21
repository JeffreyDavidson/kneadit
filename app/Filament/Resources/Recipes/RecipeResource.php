<?php

namespace App\Filament\Resources\Recipes;

use App\Enums\Platform\SubscriptionTier;
use App\Filament\Concerns\ShowsUpgradeBadge;
use App\Filament\Resources\Recipes\Pages\ListRecipes;
use App\Filament\Resources\Recipes\Schemas\RecipeForm;
use App\Filament\Resources\Recipes\Tables\RecipesTable;
use App\Models\Inventory\Recipe;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Laravel\Pennant\Feature;

class RecipeResource extends Resource
{
    use ShowsUpgradeBadge;

    #[\Override]
    protected static ?string $model = Recipe::class;

    #[\Override]
    protected static ?string $recordTitleAttribute = 'name';

    #[\Override]
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBeaker;

    #[\Override]
    protected static string|\UnitEnum|null $navigationGroup = 'Tools';

    #[\Override]
    protected static ?int $navigationSort = 5;

    #[\Override]
    public static function form(Schema $schema): Schema
    {
        return RecipeForm::configure($schema);
    }

    #[\Override]
    public static function table(Table $table): Table
    {
        return RecipesTable::configure($table);
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
        return [
            //
        ];
    }

    #[\Override]
    public static function getGloballySearchableAttributes(): array
    {
        return ['name'];
    }

    /** @param Recipe $record */
    #[\Override]
    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return $record->name;
    }

    /** @param Recipe $record */
    #[\Override]
    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Product' => $record->product->name ?? 'N/A',
            'Prep Time' => ($record->prep_time_minutes ?? 0).' min',
        ];
    }

    #[\Override]
    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with('product');
    }

    #[\Override]
    public static function getPages(): array
    {
        return [
            'index' => ListRecipes::route('/'),
        ];
    }
}
