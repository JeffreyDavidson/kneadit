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
                Grid::make(2)->schema([
                    TextInput::make('pageContent.menu.hero_eyebrow')
                        ->label('Hero Eyebrow')
                        ->helperText('Use {{store_name}} for bakery name'),
                    TextInput::make('pageContent.menu.hero_title')
                        ->label('Hero Title'),
                ]),
                Textarea::make('pageContent.menu.hero_subtitle')
                    ->label('Hero Subtitle')
                    ->rows(2),
                TextInput::make('pageContent.menu.category_eyebrow')
                    ->label('Category Section Eyebrow'),
                Section::make('Call to Action')->schema([
                    Grid::make(2)->schema([
                        TextInput::make('pageContent.menu.cta_script')
                            ->label('CTA Script Text'),
                        TextInput::make('pageContent.menu.cta_button')
                            ->label('CTA Button Text'),
                    ]),
                    TextInput::make('pageContent.menu.cta_heading')
                        ->label('CTA Heading'),
                    TextInput::make('pageContent.menu.cta_description')
                        ->label('CTA Description')
                        ->helperText('Use {{lead_time}} for order lead time hours'),
                ])->compact(),
            ]);
    }
}
