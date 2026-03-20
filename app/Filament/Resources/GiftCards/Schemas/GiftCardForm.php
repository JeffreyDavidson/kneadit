<?php

namespace App\Filament\Resources\GiftCards\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class GiftCardForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Purchaser Information')
                    ->columnSpanFull()
                    ->components([
                        Grid::make(2)
                            ->components([
                                TextInput::make('purchaser_name')
                                    ->required()
                                    ->maxLength(255)
                                    ->label('Name'),

                                TextInput::make('purchaser_email')
                                    ->required()
                                    ->email()
                                    ->maxLength(255)
                                    ->label('Email'),
                            ]),
                    ]),

                Section::make('Recipient Information')
                    ->columnSpanFull()
                    ->components([
                        Grid::make(2)
                            ->components([
                                TextInput::make('recipient_name')
                                    ->maxLength(255)
                                    ->label('Recipient Name'),

                                TextInput::make('recipient_email')
                                    ->email()
                                    ->maxLength(255)
                                    ->label('Recipient Email'),
                            ]),

                        Textarea::make('message')
                            ->label('Gift Message')
                            ->rows(3),
                    ]),

                Section::make('Card Details')
                    ->columnSpanFull()
                    ->components([
                        Grid::make(2)
                            ->components([
                                TextInput::make('initial_balance')
                                    ->required()
                                    ->numeric()
                                    ->minValue(1)
                                    ->step(0.01)
                                    ->prefix('$')
                                    ->label('Amount'),

                                DatePicker::make('expires_at')
                                    ->label('Expiration Date')
                                    ->placeholder('Never expires'),
                            ]),
                    ]),
            ]);
    }
}
