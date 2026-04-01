<?php

namespace App\Filament\Resources\Settings\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Setting Details')
                    ->columnSpanFull()
                    ->components([
                        TextInput::make('key')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->helperText('Unique identifier for this setting'),

                        Textarea::make('value')
                            ->rows(4)
                            ->helperText('The value for this setting'),
                    ]),
            ]);
    }
}
