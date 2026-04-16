<?php

namespace App\Filament\Pages\Settings\Schemas\PageContent;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs\Tab;

class OrderTabSchema
{
    public static function make(): Tab
    {
        return Tab::make('Order')
            ->schema([
                Section::make('Buttons')->schema([
                    Grid::make(2)->schema([
                        TextInput::make('pageContent.order.place_order_button')
                            ->label('"Place Order" Button')
                            ->placeholder('Place Order →'),
                        TextInput::make('pageContent.order.apply_button')
                            ->label('"Apply" Button (coupon / gift card)')
                            ->placeholder('Apply'),
                    ]),
                ])->compact(),
                Section::make('Empty Cart')->schema([
                    Grid::make(2)->schema([
                        TextInput::make('pageContent.order.empty_cart_heading')
                            ->label('Empty Cart Heading')
                            ->placeholder('Your cart is empty'),
                        TextInput::make('pageContent.order.empty_cart_subtext')
                            ->label('Empty Cart Subtext')
                            ->placeholder('Add items to get started'),
                    ]),
                ])->compact(),
                Section::make('Flash Messages')->schema([
                    TextInput::make('pageContent.order.flash_success')
                        ->label('Order Submitted Success Message')
                        ->placeholder('Order submitted successfully!'),
                    TextInput::make('pageContent.order.flash_full')
                        ->label('Date Fully Booked Message')
                        ->placeholder('Sorry, this date is fully booked. Please choose another date.'),
                ])->compact(),
            ]);
    }
}
