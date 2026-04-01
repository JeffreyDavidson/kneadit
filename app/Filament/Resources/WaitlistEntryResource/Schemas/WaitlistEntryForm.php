<?php

namespace App\Filament\Resources\WaitlistEntryResource\Schemas;

use App\Enums\Customers\WaitlistStatus;
use App\Models\Inventory\Product;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class WaitlistEntryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Customer Information')
                    ->columnSpanFull()
                    ->components([
                        TextInput::make('customer_name')
                            ->required()
                            ->maxLength(255),

                        Grid::make(2)
                            ->components([
                                TextInput::make('customer_email')
                                    ->email()
                                    ->required()
                                    ->maxLength(255),

                                TextInput::make('customer_phone')
                                    ->tel()
                                    ->maxLength(255),
                            ]),
                    ]),

                Section::make('Request Details')
                    ->columnSpanFull()
                    ->components([
                        Grid::make(2)
                            ->components([
                                DatePicker::make('requested_date')
                                    ->required(),

                                Select::make('product_id')
                                    ->label('Product')
                                    ->options(Product::query()->pluck('name', 'id'))
                                    ->searchable()
                                    ->nullable(),
                            ]),

                        Select::make('status')
                            ->options(WaitlistStatus::class)
                            ->required()
                            ->default(WaitlistStatus::Waiting->value),

                        Textarea::make('notes')
                            ->rows(3),
                    ]),
            ]);
    }
}
