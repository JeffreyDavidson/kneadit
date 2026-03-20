<?php

namespace App\Filament\Resources\ContactMessages\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ContactMessageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Contact Information')
                    ->columnSpanFull()
                    ->components([
                        Grid::make(2)
                            ->components([
                                TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),

                                TextInput::make('email')
                                    ->required()
                                    ->email()
                                    ->maxLength(255),
                            ]),

                        TextInput::make('subject')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Textarea::make('message')
                            ->required()
                            ->rows(6)
                            ->columnSpanFull(),

                        Toggle::make('is_read')
                            ->default(false),
                    ]),
            ]);
    }
}
