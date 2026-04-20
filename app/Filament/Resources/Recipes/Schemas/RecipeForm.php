<?php

namespace App\Filament\Resources\Recipes\Schemas;

use App\Filament\Forms\Components\MoneyInput;
use App\Models\Inventory\Ingredient;
use App\Models\Inventory\Product;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RecipeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Recipe Details')
                    ->columnSpanFull()
                    ->components([
                        Grid::make(2)
                            ->components([
                                TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),

                                Select::make('product_id')
                                    ->label('Product')
                                    ->options(Product::query()->pluck('name', 'id'))
                                    ->searchable(),
                            ]),

                        Grid::make(2)
                            ->components([
                                TextInput::make('prep_time_minutes')
                                    ->label('Prep Time (minutes)')
                                    ->numeric()
                                    ->required()
                                    ->minValue(1),

                                MoneyInput::make('cost')
                                    ->helperText('Estimated cost of ingredients'),
                            ]),
                    ]),

                Section::make('Ingredients')
                    ->columnSpanFull()
                    ->components([
                        Repeater::make('ingredients')
                            ->schema([
                                Grid::make(3)
                                    ->components([
                                        TextInput::make('name')
                                            ->required(),

                                        TextInput::make('quantity')
                                            ->required(),

                                        TextInput::make('unit')
                                            ->placeholder('cups, tsp, lbs, etc.')
                                            ->required(),
                                    ]),
                            ])
                            ->columns(1)
                            ->addActionLabel('Add Ingredient')
                            ->reorderable()
                            ->collapsible(),
                    ]),

                Section::make('Linked Inventory Ingredients')
                    ->columnSpanFull()
                    ->description('Link to tracked ingredients for automatic stock management')
                    ->components([
                        Repeater::make('inventoryIngredients')
                            ->relationship()
                            ->schema([
                                Grid::make(3)
                                    ->components([
                                        Select::make('ingredient_id')
                                            ->label('Ingredient')
                                            ->options(Ingredient::query()->pluck('name', 'id'))
                                            ->searchable()
                                            ->required(),

                                        TextInput::make('quantity')
                                            ->numeric()
                                            ->required()
                                            ->step(0.01),

                                        Select::make('unit')
                                            ->options([
                                                'oz' => 'oz',
                                                'lbs' => 'lbs',
                                                'g' => 'g',
                                                'kg' => 'kg',
                                                'cups' => 'cups',
                                                'tbsp' => 'tbsp',
                                                'tsp' => 'tsp',
                                                'ml' => 'ml',
                                                'l' => 'l',
                                                'each' => 'each',
                                                'dozen' => 'dozen',
                                            ])
                                            ->required(),
                                    ]),
                            ])
                            ->columns(1)
                            ->addActionLabel('Link Ingredient')
                            ->reorderable()
                            ->collapsible(),
                    ]),

                Section::make('Instructions')
                    ->columnSpanFull()
                    ->components([
                        Textarea::make('instructions')
                            ->required()
                            ->rows(6),
                    ]),
            ]);
    }
}
