<?php

namespace App\Filament\Pages\Settings\Schemas\PageContent;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs\Tab;

class MenuTabSchema
{
    public static function make(): Tab
    {
        return Tab::make('Menu')
            ->schema([
                Section::make('Hero')->schema([
                    Grid::make(2)->schema([
                        TextInput::make('pageContent.menu.hero_eyebrow')
                            ->label('Eyebrow')
                            ->helperText('Use {{store_name}} for bakery name'),
                        TextInput::make('pageContent.menu.hero_title')
                            ->label('Title'),
                    ]),
                    Textarea::make('pageContent.menu.hero_subtitle')
                        ->label('Subtitle')
                        ->rows(2),
                    TextInput::make('pageContent.menu.category_eyebrow')
                        ->label('Category Eyebrow'),
                ])->compact(),
                Section::make('Call to Action')->schema([
                    Grid::make(2)->schema([
                        TextInput::make('pageContent.menu.cta_script')
                            ->label('Script Text'),
                        TextInput::make('pageContent.menu.cta_button')
                            ->label('Button Text'),
                    ]),
                    TextInput::make('pageContent.menu.cta_heading')
                        ->label('Heading'),
                    TextInput::make('pageContent.menu.cta_description')
                        ->label('Description')
                        ->helperText('Use {{lead_time}} for order lead time hours'),
                ])->compact(),
                Section::make('Buttons & Messages')->schema([
                    TextInput::make('pageContent.menu.add_to_order_button')
                        ->label('"Add to Order" Button')
                        ->placeholder('Add to Order'),
                    TextInput::make('pageContent.menu.empty_message')
                        ->label('Empty Menu Message')
                        ->placeholder('Our menu is being updated. Check back soon.'),
                ])->compact(),
            ]);
    }
}
