<?php

namespace App\Filament\Pages\Settings\Schemas\PageContent;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs\Tab;

class CateringTabSchema
{
    public static function make(): Tab
    {
        return Tab::make('Catering')
            ->schema([
                Grid::make(2)->schema([
                    TextInput::make('pageContent.catering.hero_eyebrow')
                        ->label('Hero Eyebrow'),
                    TextInput::make('pageContent.catering.hero_title')
                        ->label('Hero Title'),
                ]),
                TextInput::make('pageContent.catering.hero_subtitle')
                    ->label('Hero Subtitle'),
                TextInput::make('pageContent.catering.hero_button')
                    ->label('Hero Button Text'),
                Section::make('Occasions')->schema([
                    Grid::make(2)->schema([
                        TextInput::make('pageContent.catering.occasions_eyebrow')
                            ->label('Eyebrow'),
                        TextInput::make('pageContent.catering.occasions_heading')
                            ->label('Heading'),
                    ]),
                    Repeater::make('pageContent.catering.occasions')
                        ->label('Occasion Cards')
                        ->schema([
                            TextInput::make('title')->required(),
                            Textarea::make('description')->rows(2)->required(),
                        ])
                        ->defaultItems(3)
                        ->maxItems(6),
                ])->compact(),
                Section::make('Process Steps')->schema([
                    Grid::make(2)->schema([
                        TextInput::make('pageContent.catering.process_eyebrow')
                            ->label('Eyebrow'),
                        TextInput::make('pageContent.catering.process_heading')
                            ->label('Heading'),
                    ]),
                    Repeater::make('pageContent.catering.process_steps')
                        ->label('Steps')
                        ->schema([
                            TextInput::make('title')->required(),
                            TextInput::make('description')->required(),
                        ])
                        ->defaultItems(4)
                        ->maxItems(6),
                ])->compact(),
                Section::make('Testimonial')->schema([
                    TextInput::make('pageContent.catering.testimonial_script')
                        ->label('Script Text'),
                    Textarea::make('pageContent.catering.testimonial_quote')
                        ->label('Quote')
                        ->rows(3),
                    TextInput::make('pageContent.catering.testimonial_attribution')
                        ->label('Attribution'),
                ])->compact(),
                Grid::make(2)->schema([
                    TextInput::make('pageContent.catering.form_eyebrow')
                        ->label('Form Eyebrow'),
                    TextInput::make('pageContent.catering.form_heading')
                        ->label('Form Heading'),
                ]),
                Section::make('Buttons & Messages')->schema([
                    TextInput::make('pageContent.catering.submit_button')
                        ->label('"Submit Inquiry" Button')
                        ->placeholder('Submit Inquiry'),
                    TextInput::make('pageContent.catering.flash_success')
                        ->label('Inquiry Received Message')
                        ->placeholder("Thank you for your inquiry! We'll review your request and get back to you with a custom quote soon."),
                ])->compact(),
            ]);
    }
}
