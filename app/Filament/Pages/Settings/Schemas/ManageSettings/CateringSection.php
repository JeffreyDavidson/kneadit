<?php

namespace App\Filament\Pages\Settings\Schemas\ManageSettings;

use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;

class CateringSection
{
    public static function make(): Section
    {
        return Section::make('Catering')
            ->description('Configure catering inquiry options')
            ->schema([
                TagsInput::make('catering_event_types')
                    ->label('Event Types')
                    ->placeholder('Add an event type')
                    ->helperText('Customers select from these options on the catering inquiry form (e.g. Wedding, Corporate Event, Birthday Party).')
                    ->reorderable()
                    ->columnSpanFull(),

                TextInput::make('catering_deposit_percent')
                    ->label('Deposit Percent')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100)
                    ->default(25)
                    ->helperText('Used to compute the suggested deposit shown in quote emails and the "Mark Deposit Received" admin action. 0 disables deposit messaging.'),
            ]);
    }
}
