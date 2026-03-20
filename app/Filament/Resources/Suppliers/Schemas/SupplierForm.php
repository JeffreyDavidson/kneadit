<?php

namespace App\Filament\Resources\Suppliers\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SupplierForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Supplier Details')
                    ->columnSpanFull()
                    ->components([
                        Grid::make(2)
                            ->components([
                                TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),

                                TextInput::make('contact_name')
                                    ->label('Contact Name')
                                    ->maxLength(255),
                            ]),

                        Grid::make(2)
                            ->components([
                                TextInput::make('email')
                                    ->email()
                                    ->maxLength(255),

                                TextInput::make('phone')
                                    ->tel()
                                    ->maxLength(255),
                            ]),

                        TextInput::make('website')
                            ->url()
                            ->maxLength(255),

                        Textarea::make('address')
                            ->rows(2),

                        Textarea::make('notes')
                            ->rows(3),

                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                    ]),
            ]);
    }
}
