<?php

namespace App\Filament\Resources\Incomes\Schemas;

use App\Enums\Financial\IncomeSource;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class IncomeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Income Details')
                    ->columnSpanFull()
                    ->components([
                        TextInput::make('description')
                            ->required()
                            ->maxLength(255),

                        Grid::make(2)
                            ->components([
                                TextInput::make('amount')
                                    ->required()
                                    ->numeric()
                                    ->prefix('$')
                                    ->step(0.01),

                                Select::make('source')
                                    ->options(IncomeSource::class)
                                    ->required(),
                            ]),

                        DatePicker::make('date')
                            ->required()
                            ->default(now()),

                        Textarea::make('notes')
                            ->rows(3),
                    ]),
            ]);
    }
}
