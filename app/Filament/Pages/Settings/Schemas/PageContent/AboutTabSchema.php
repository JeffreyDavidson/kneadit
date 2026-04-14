<?php

namespace App\Filament\Pages\Settings\Schemas\PageContent;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs\Tab;

class AboutTabSchema
{
    public static function make(): Tab
    {
        return Tab::make('About')
            ->schema([
                TextInput::make('pageContent.about.hero_eyebrow')
                    ->label('Hero Eyebrow'),
                TextInput::make('pageContent.about.story_eyebrow')
                    ->label('Story Section Eyebrow'),
                Section::make('Values Section')->schema([
                    Grid::make(2)->schema([
                        TextInput::make('pageContent.about.values_eyebrow')
                            ->label('Values Eyebrow'),
                        TextInput::make('pageContent.about.values_heading')
                            ->label('Values Heading'),
                    ]),
                    Repeater::make('pageContent.about.values')
                        ->label('Value Cards')
                        ->schema([
                            TextInput::make('title')->required(),
                            Textarea::make('description')->rows(2)->required(),
                        ])
                        ->defaultItems(3)
                        ->maxItems(6),
                ])->compact(),
                Grid::make(2)->schema([
                    TextInput::make('pageContent.about.location_eyebrow')
                        ->label('Location Eyebrow'),
                    TextInput::make('pageContent.about.social_eyebrow')
                        ->label('Social Eyebrow'),
                ]),
                Section::make('Call to Action')->schema([
                    Grid::make(3)->schema([
                        TextInput::make('pageContent.about.cta_script')
                            ->label('CTA Script Text'),
                        TextInput::make('pageContent.about.cta_heading')
                            ->label('CTA Heading'),
                        TextInput::make('pageContent.about.cta_button')
                            ->label('CTA Button Text'),
                    ]),
                ])->compact(),
            ]);
    }
}
