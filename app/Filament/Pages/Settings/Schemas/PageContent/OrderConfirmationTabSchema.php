<?php

namespace App\Filament\Pages\Settings\Schemas\PageContent;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Tabs\Tab;

class OrderConfirmationTabSchema
{
    public static function make(): Tab
    {
        return Tab::make('Order Confirmation')
            ->schema([
                Grid::make(2)->schema([
                    TextInput::make('pageContent.order_confirmation.hero_eyebrow')
                        ->label('Hero Eyebrow'),
                    TextInput::make('pageContent.order_confirmation.hero_title')
                        ->label('Hero Title'),
                ]),
                Textarea::make('pageContent.order_confirmation.hero_description')
                    ->label('Hero Description')
                    ->rows(2),
                TextInput::make('pageContent.order_confirmation.details_heading')
                    ->label('Order Details Heading'),
                Grid::make(2)->schema([
                    TextInput::make('pageContent.order_confirmation.journey_eyebrow')
                        ->label('Journey Eyebrow'),
                    TextInput::make('pageContent.order_confirmation.journey_heading')
                        ->label('Journey Heading'),
                ]),
            ]);
    }
}
