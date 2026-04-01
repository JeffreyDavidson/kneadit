<?php

namespace App\Filament\Central\Resources\ScheduledCheckinResource\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ScheduledCheckinForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('days_after_signup')
                    ->label('Days After Signup')
                    ->numeric()
                    ->required()
                    ->minValue(1),
                TextInput::make('subject')
                    ->required()
                    ->maxLength(255),
                Textarea::make('body')
                    ->required()
                    ->columnSpanFull(),
                Toggle::make('is_active')
                    ->label('Active')
                    ->default(true),
            ]);
    }
}
