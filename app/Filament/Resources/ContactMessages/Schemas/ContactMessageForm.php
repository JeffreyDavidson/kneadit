<?php

namespace App\Filament\Resources\ContactMessages\Schemas;

use Filament\Components\Textarea;
use Filament\Components\TextInput;
use Filament\Components\Toggle;
use Filament\Layouts\Grid;
use Filament\Layouts\Section;
use Filament\Schemas\Schema;

class ContactMessageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Contact Information')
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
