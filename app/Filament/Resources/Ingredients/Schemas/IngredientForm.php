<?php

namespace App\Filament\Resources\Ingredients\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class IngredientForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Ingredient Details')
                    ->columnSpanFull()
                    ->components([
                        Grid::make(2)
                            ->components([
                                TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),

                                Select::make('unit')
                                    ->required()
                                    ->options([
                                        'oz' => 'Ounces (oz)',
                                        'lbs' => 'Pounds (lbs)',
                                        'g' => 'Grams (g)',
                                        'kg' => 'Kilograms (kg)',
                                        'cups' => 'Cups',
                                        'tbsp' => 'Tablespoons',
                                        'tsp' => 'Teaspoons',
                                        'ml' => 'Milliliters (ml)',
                                        'l' => 'Liters (l)',
                                        'each' => 'Each',
                                        'dozen' => 'Dozen',
                                    ])
                                    ->searchable(),
                            ]),

                        Grid::make(2)
                            ->components([
                                TextInput::make('current_stock')
                                    ->numeric()
                                    ->default(0)
                                    ->required()
                                    ->step(0.01),

                                TextInput::make('low_stock_threshold')
                                    ->numeric()
                                    ->default(0)
                                    ->required()
                                    ->step(0.01)
                                    ->helperText('Alert when stock falls below this level'),
                            ]),

                        Grid::make(2)
                            ->components([
                                TextInput::make('cost_per_unit')
                                    ->numeric()
                                    ->prefix('$')
                                    ->step(0.01),

                                TextInput::make('supplier')
                                    ->maxLength(255),
                            ]),

                        Textarea::make('notes')
                            ->rows(3),
                    ]),
            ]);
    }
}
