<?php

namespace App\Filament\Resources\Expenses\Schemas;

use App\Enums\Financial\ExpenseCategory;
use App\Filament\Forms\Components\MoneyInput;
use App\Filament\Support\AllowedFileTypes;
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
                                MoneyInput::make('amount')
                                    ->required(),

                                Select::make('category')
                                    ->options(ExpenseCategory::class)
                                    ->required(),
                            ]),

                        DatePicker::make('date')
                            ->required()
                            ->default(now()),

                        FileUpload::make('receipt_image')
                            ->label('Receipt Image')
                            ->image()
                            ->acceptedFileTypes(AllowedFileTypes::IMAGES)
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

                                MoneyInput::make('deductible_amount')
                                    ->label('Deductible Amount')
                                    ->disabled()
                                    ->dehydrated(false),
                            ]),
                    ]),
            ]);
    }
}
