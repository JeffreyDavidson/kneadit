<?php

namespace App\Filament\Pages\Settings\Schemas\ManageSettings;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;

class OrderJourneySection
{
    public static function make(): Section
    {
        return Section::make('Order Journey')
            ->description('Customize the "What Happens Next" steps shown on the order confirmation page. The final step supports separate delivery and pickup copy.')
            ->schema([
                Repeater::make('order_journey_steps')
                    ->label('Journey Steps')
                    ->schema([
                        TextInput::make('title')
                            ->required(),
                        TextInput::make('description')
                            ->label('Description (general/pickup)'),
                        TextInput::make('description_delivery')
                            ->label('Description (delivery variant)')
                            ->helperText('Leave blank if same as above'),
                        TextInput::make('description_pickup')
                            ->label('Description (pickup variant)')
                            ->helperText('Leave blank if same as above'),
                    ])
                    ->defaultItems(3)
                    ->maxItems(6)
                    ->columnSpanFull(),
            ]);
    }
}
