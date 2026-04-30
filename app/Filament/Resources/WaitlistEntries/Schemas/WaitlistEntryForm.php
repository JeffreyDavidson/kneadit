<?php

namespace App\Filament\Resources\WaitlistEntries\Schemas;

use App\Enums\Customers\WaitlistStatus;
use App\Filament\Forms\Components\ContactFields;
use App\Models\Inventory\Product;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
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
                        ContactFields::name(),

                        Grid::make(2)
                            ->components([
                                ContactFields::email(),
                                ContactFields::phone(),
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
                            ->default(WaitlistStatus::Waiting),

                        Textarea::make('notes')
                            ->rows(3),
                    ]),
            ]);
    }
}
