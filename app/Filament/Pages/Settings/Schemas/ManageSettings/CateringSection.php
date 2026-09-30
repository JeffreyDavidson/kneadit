<?php

declare(strict_types=1);

namespace App\Filament\Pages\Settings\Schemas\ManageSettings;

use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;

class CateringSection
{
    public static function make(): Section
    {
        return Section::make('Catering')
            ->description('Configure catering inquiry options')
            ->schema([
                Toggle::make('catering_enabled')
                    ->label('Accept Catering Inquiries')
                    ->helperText('Show the catering page and inquiry form on your storefront.')
                    ->columnSpanFull(),

                TextInput::make('catering_minimum_guests')
                    ->label('Catering Minimum Guests')
                    ->numeric()
                    ->minValue(1)
                    ->default(10)
                    ->helperText('Smallest guest count accepted on the catering inquiry form.'),

                TextInput::make('catering_lead_time_days')
                    ->label('Catering Lead Time (days)')
                    ->numeric()
                    ->minValue(0)
                    ->default(14)
                    ->helperText('How many days ahead customers must book an event. 0 allows same-day events.'),

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
