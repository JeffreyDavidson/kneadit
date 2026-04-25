<?php

namespace App\Filament\Central\Resources\PlatformSettings\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PlatformSettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('key')
                    ->required()
                    ->maxLength(190)
                    ->unique(ignoreRecord: true)
                    ->disabled(fn ($record) => $record !== null)
                    ->dehydrated()
                    ->helperText('Snake-case identifier. Cannot be changed after creation.')
                    ->placeholder('feature_x_enabled'),
                Textarea::make('value')
                    ->rows(4)
                    ->maxLength(65535)
                    ->helperText('Stored as a string. JSON, numbers, "1"/"0" all work — the consumer interprets the value.')
                    ->columnSpanFull(),
            ]);
    }
}
