<?php

declare(strict_types=1);

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

                        TextInput::make('store_city')
                            ->label('Store City')
                            ->placeholder('City'),

                        TextInput::make('store_state')
                            ->label('Store State')
                            ->placeholder('State'),

                        TextInput::make('store_zip')
                            ->label('Store ZIP Code')
                            ->placeholder('12345')
                            ->helperText('City, state and ZIP appear on PayPal invoices.'),

                        TextInput::make('store_website')
                            ->label('Store Website')
                            ->url()
                            ->placeholder('https://yourbakery.com')
                            ->helperText('Shown on printed invoices.'),
                    ]),
            ]);
    }
}
