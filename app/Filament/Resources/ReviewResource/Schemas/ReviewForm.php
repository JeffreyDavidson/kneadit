<?php

namespace App\Filament\Resources\ReviewResource\Schemas;

use App\Models\Inventory\Product;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ReviewForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Customer Information')
                    ->columnSpanFull()
                    ->components([
                        Grid::make(2)
                            ->components([
                                TextInput::make('customer_name')
                                    ->required()
                                    ->maxLength(255),

                                TextInput::make('customer_email')
                                    ->email()
                                    ->required()
                                    ->maxLength(255),
                            ]),
                    ]),

                Section::make('Review Details')
                    ->columnSpanFull()
                    ->components([
                        Select::make('product_id')
                            ->label('Product')
                            ->options(Product::query()->pluck('name', 'id'))
                            ->searchable(),

                        Select::make('rating')
                            ->options([
                                1 => '1 Star',
                                2 => '2 Stars',
                                3 => '3 Stars',
                                4 => '4 Stars',
                                5 => '5 Stars',
                            ])
                            ->required(),

                        Textarea::make('comment')
                            ->rows(4),
                    ]),

                Section::make('Moderation')
                    ->columnSpanFull()
                    ->components([
                        Grid::make(2)
                            ->components([
                                Toggle::make('is_approved')
                                    ->label('Approved')
                                    ->default(false),

                                Toggle::make('is_featured')
                                    ->label('Featured')
                                    ->default(false),
                            ]),
                    ]),
            ]);
    }
}
