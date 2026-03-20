<?php

namespace App\Filament\Resources\Expenses\Schemas;

use App\Models\Expense;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ExpenseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Expense Details')
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

                                Select::make('category')
                                    ->options(Expense::CATEGORIES)
                                    ->required(),
                            ]),

                        DatePicker::make('date')
                            ->required()
                            ->default(now()),

                        FileUpload::make('receipt_image')
                            ->label('Receipt Image')
                            ->image()
                            ->directory('receipts')
                            ->maxSize(5120), // 5MB

                        Textarea::make('notes')
                            ->rows(3),

                        Grid::make(2)
                            ->components([
                                TextInput::make('business_percentage')
                                    ->label('Business Percentage')
                                    ->required()
                                    ->numeric()
                                    ->default(100)
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->suffix('%'),

                                TextInput::make('deductible_amount')
                                    ->label('Deductible Amount')
                                    ->numeric()
                                    ->prefix('$')
                                    ->step(0.01)
                                    ->disabled()
                                    ->dehydrated(false),
                            ]),
                    ]),
            ]);
    }
}
