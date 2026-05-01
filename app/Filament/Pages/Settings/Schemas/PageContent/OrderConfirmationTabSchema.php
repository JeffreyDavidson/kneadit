<?php

namespace App\Filament\Pages\Settings\Schemas\PageContent;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs\Tab;

class OrderConfirmationTabSchema
{
    public static function make(): Tab
    {
        return Tab::make('Order Confirmation')
            ->schema([
                Section::make('Hero')->schema([
                    Grid::make(2)->schema([
                        TextInput::make('pageContent.order_confirmation.hero_eyebrow')
                            ->label('Eyebrow'),
                        TextInput::make('pageContent.order_confirmation.hero_title')
                            ->label('Title'),
                    ]),
                    Textarea::make('pageContent.order_confirmation.hero_description')
                        ->label('Description')
                        ->rows(2),
                ])->compact(),
                Section::make('Order Details')->schema([
                    TextInput::make('pageContent.order_confirmation.details_heading')
                        ->label('Heading'),
                ])->compact(),
                Section::make('Journey')->schema([
                    Grid::make(2)->schema([
                        TextInput::make('pageContent.order_confirmation.journey_eyebrow')
                            ->label('Eyebrow'),
                        TextInput::make('pageContent.order_confirmation.journey_heading')
                            ->label('Heading'),
                    ]),
                ])->compact(),
            ]);
    }
}
