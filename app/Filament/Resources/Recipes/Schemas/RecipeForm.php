<?php

namespace App\Filament\Resources\Recipes\Schemas;

use App\Enums\Inventory\MeasurementUnit;
use App\Filament\Forms\Components\MoneyInput;
use App\Models\Inventory\Ingredient;
use App\Models\Inventory\Product;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class RecipeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            self::detailsSection(),
            self::ingredientsSection(),
            self::inventoryIngredientsSection(),
            self::instructionsSection(),
        ]);
    }

    private static function detailsSection(): Section
    {
        return Section::make('Recipe Details')
            ->columnSpanFull()
            ->components([
                Grid::make(2)->components([
                    TextInput::make('name')
                        ->required()
                        ->maxLength(255),

                    Select::make('product_id')
                        ->label('Product')
                        ->options(Product::query()->pluck('name', 'id'))
                        ->searchable(),
                ]),

                Grid::make(2)->components([
                    TextInput::make('prep_time_minutes')
                        ->label('Prep Time (minutes)')
                        ->numeric()
                        ->required()
                        ->minValue(1),

                    MoneyInput::make('cost')
                        ->helperText('Estimated cost of ingredients'),
                ]),
            ]);
    }

    private static function ingredientsSection(): Section
    {
        return Section::make('Ingredients')
            ->columnSpanFull()
            ->components([
                Repeater::make('ingredients')
                    ->schema([
                        Grid::make(3)->components([
                            TextInput::make('name')->required(),
                            TextInput::make('quantity')->required(),
                            TextInput::make('unit')
                                ->placeholder('cups, tsp, lbs, etc.')
                                ->required(),
                        ]),
                    ])
                    ->columns(1)
                    ->addActionLabel('Add Ingredient')
                    ->reorderable()
                    ->collapsible(),
            ]);
    }

    private static function inventoryIngredientsSection(): Section
    {
        return Section::make('Linked Inventory Ingredients')
            ->columnSpanFull()
            ->description('Link to tracked ingredients for automatic stock management')
            ->components([
                Repeater::make('inventoryIngredients')
                    ->relationship()
                    ->schema([
                        Grid::make(3)->components([
                            Select::make('ingredient_id')
                                ->label('Ingredient')
                                ->options(fn (Get $get): array => Ingredient::query()
                                    ->where('is_active', true)
                                    ->orWhereKey($get('ingredient_id'))
                                    ->orderBy('name')
                                    ->pluck('name', 'id')
                                    ->all())
                                ->searchable()
                                ->live()
                                ->afterStateUpdated(fn (Set $set, ?string $state): mixed => $set('unit', Ingredient::query()->find($state)?->measurement_unit?->value))
                                ->required(),

                            TextInput::make('quantity')
                                ->numeric()
                                ->required()
                                ->step(0.01),

                            Select::make('unit')
                                ->options(fn (Get $get): array => self::unitOptions($get('ingredient_id')))
                                ->hint(fn (Get $get): ?string => self::unitHint($get('ingredient_id'), $get('unit')))
                                ->hintColor('warning')
                                ->hintIcon(Heroicon::ExclamationTriangle)
                                ->required(),
                        ]),
                    ])
                    ->columns(1)
                    ->addActionLabel('Link Ingredient')
                    ->reorderable()
                    ->collapsible(),
            ]);
    }

    private static function instructionsSection(): Section
    {
        return Section::make('Instructions')
            ->columnSpanFull()
            ->components([
                Textarea::make('instructions')
                    ->required()
                    ->rows(6),
            ]);
    }

    /**
     * The units a linked line can use: only those in the stock unit's dimension, since
     * cups of flour can't be converted to a stock held in pounds without a density.
     *
     * @return array<string, string>
     */
    public static function unitOptions(mixed $ingredientId): array
    {
        if (blank($ingredientId)) {
            return MeasurementUnit::options();
        }

        return MeasurementUnit::options(Ingredient::query()->whereKey($ingredientId)->first()?->measurement_unit?->dimension());
    }

    private static function unitHint(mixed $ingredientId, mixed $unit): ?string
    {
        if (! is_string($unit) || array_key_exists($unit, self::unitOptions($ingredientId))) {
            return null;
        }

        return 'This unit can\'t be converted to the ingredient\'s stock unit, so the line is skipped when stock is checked and deducted. Pick another unit.';
    }
}
