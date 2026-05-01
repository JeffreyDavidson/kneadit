<?php

namespace App\Filament\Pages\Settings\Schemas\ManageSettings;

use App\Filament\Forms\Components\MoneyInput;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;

class GiftCardsSection
{
    public static function make(): Section
    {
        return Section::make('Gift Cards')
            ->description('Configure gift card purchase options on your storefront')
            ->schema([
                Grid::make(2)
                    ->schema([
                        TextInput::make('gift_card_preset_amounts')
                            ->label('Preset Amounts')
                            ->placeholder('10,25,50,100')
                            ->helperText('Comma-separated dollar amounts shown as quick-select buttons'),

                        MoneyInput::make('gift_card_default_amount')
                            ->label('Default Selected Amount')
                            ->default(25)
                            ->helperText('The amount pre-selected when the page loads'),
                    ]),
            ]);
    }
}
