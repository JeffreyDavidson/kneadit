<?php

namespace App\Filament\Pages\Settings\Schemas\ManageSettings;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;

class StoreInformationSection
{
    public static function make(): Section
    {
        return Section::make('Store Information')
            ->description('Basic information about your bakery')
            ->schema([
                Grid::make(2)
                    ->schema([
                        TextInput::make('store_name')
                            ->label('Store Name')
                            ->required()
                            ->placeholder('Your Bakery Name'),

                        TextInput::make('store_email')
                            ->label('Store Email')
                            ->email()
                            ->placeholder('contact@yourbakery.com'),

                        TextInput::make('store_phone')
                            ->label('Store Phone')
                            ->tel()
                            ->placeholder('+1 (555) 123-4567'),

                        TextInput::make('store_address')
                            ->label('Store Address')
                            ->placeholder('123 Baker Street, City, State 12345')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
