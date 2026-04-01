<?php

namespace App\Filament\Resources\CustomerResource\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CustomerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Customer Details')
                    ->columnSpanFull()
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

                        Grid::make(2)
                            ->components([
                                TextInput::make('phone')
                                    ->tel()
                                    ->maxLength(255),

                                DatePicker::make('birthday')
                                    ->label('Birthday')
                                    ->displayFormat('M j')
                                    ->maxDate(now()),
                            ]),

                        Textarea::make('notes')
                            ->rows(3),

                        Textarea::make('customerProfile.notes')
                            ->label('Customer Profile Notes')
                            ->rows(2)
                            ->helperText('Additional notes about customer preferences, allergies, etc.'),
                    ]),

                Section::make('Address')
                    ->columnSpanFull()
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
