<?php

namespace App\Filament\Resources\Customers\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;

class CustomerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Customer Details')
                    ->components([
                        Grid::make(2)
                            ->components([
                                TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),

                                TextInput::make('email')
                                    ->email()
                                    ->required()
                                    ->unique(ignoreRecord: true),
                            ]),

                        TextInput::make('phone')
                            ->tel()
                            ->maxLength(255),

                        Textarea::make('notes')
                            ->rows(3),
                    ]),

                Section::make('Address')
                    ->components([
                        TextInput::make('address')
                            ->maxLength(255),

                        Grid::make(3)
                            ->components([
                                TextInput::make('city')
                                    ->maxLength(255),

                                TextInput::make('state')
                                    ->maxLength(255),

                                TextInput::make('zip')
                                    ->maxLength(255),
                            ]),
                    ]),
            ]);
    }
}
