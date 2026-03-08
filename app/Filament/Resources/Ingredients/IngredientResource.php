<?php

namespace App\Filament\Resources\Ingredients;

use App\Filament\Resources\Ingredients\Pages\CreateIngredient;
use App\Filament\Resources\Ingredients\Pages\EditIngredient;
use App\Filament\Resources\Ingredients\Pages\ListIngredients;
use App\Filament\Resources\Ingredients\Schemas\IngredientForm;
use App\Filament\Resources\Ingredients\Tables\IngredientsTable;
use App\Models\Ingredient;
use App\Traits\HasPlanGating;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class IngredientResource extends Resource
{
    use HasPlanGating;

    protected static ?string $model = Ingredient::class;
    protected static string $requiredPlan = 'pro';

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

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListIngredients::route('/'),
            'create' => CreateIngredient::route('/create'),
            'edit' => EditIngredient::route('/{record}/edit'),
        ];
    }
}
