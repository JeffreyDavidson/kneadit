<?php

namespace App\Filament\Pages\Settings\Schemas\PageContent;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs\Tab;

class OrderTrackingTabSchema
{
    public static function make(): Tab
    {
        return Tab::make('Order Tracking')
            ->schema([
                Section::make('Hero')->schema([
                    Grid::make(2)->schema([
                        TextInput::make('pageContent.order_tracking.hero_eyebrow')
                            ->label('Eyebrow'),
                        TextInput::make('pageContent.order_tracking.hero_title')
                            ->label('Title'),
                    ]),
                    TextInput::make('pageContent.order_tracking.hero_subtitle')
                        ->label('Subtitle'),
                ])->compact(),
                Section::make('Lookup Form')->schema([
                    Grid::make(2)->schema([
                        TextInput::make('pageContent.order_tracking.email_label')
                            ->label('Email Label'),
                        TextInput::make('pageContent.order_tracking.lookup_button')
                            ->label('Lookup Button Text'),
                    ]),
                ])->compact(),
                Section::make('Empty State')->schema([
                    TextInput::make('pageContent.order_tracking.empty_heading')
                        ->label('Heading'),
                    TextInput::make('pageContent.order_tracking.empty_description_prefix')
                        ->label('Description Prefix'),
                    TextInput::make('pageContent.order_tracking.empty_hint')
                        ->label('Hint Text'),
                ])->compact(),
                Section::make('Order Detail Labels')->schema([
                    Grid::make(3)->schema([
                        TextInput::make('pageContent.order_tracking.items_label')
                            ->label('Items Label'),
                        TextInput::make('pageContent.order_tracking.messages_label')
                            ->label('Messages Label'),
                        TextInput::make('pageContent.order_tracking.reorder_button')
                            ->label('Reorder Button'),
                    ]),
                ])->compact(),
                Section::make('Call to Action')->schema([
                    Grid::make(3)->schema([
                        TextInput::make('pageContent.order_tracking.cta_script')
                            ->label('Script Text'),
                        TextInput::make('pageContent.order_tracking.cta_heading')
                            ->label('Heading'),
                        TextInput::make('pageContent.order_tracking.cta_button')
                            ->label('Button Text'),
                    ]),
                ])->compact(),
            ]);
    }
}
