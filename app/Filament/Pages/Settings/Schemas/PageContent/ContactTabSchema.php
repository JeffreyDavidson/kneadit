<?php

namespace App\Filament\Pages\Settings\Schemas\PageContent;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Tabs\Tab;

class ContactTabSchema
{
    public static function make(): Tab
    {
        return Tab::make('Contact')
            ->schema([
                Grid::make(2)->schema([
                    TextInput::make('pageContent.contact.hero_eyebrow')
                        ->label('Hero Eyebrow'),
                    TextInput::make('pageContent.contact.form_eyebrow')
                        ->label('Form Section Eyebrow'),
                ]),
                Textarea::make('pageContent.contact.hero_title')
                    ->label('Hero Title')
                    ->helperText('Use line breaks for multi-line titles')
                    ->rows(2),
                Textarea::make('pageContent.contact.hero_subtitle')
                    ->label('Hero Subtitle')
                    ->rows(2),
                Grid::make(3)->schema([
                    TextInput::make('pageContent.contact.hours_eyebrow')
                        ->label('Hours Eyebrow'),
                    TextInput::make('pageContent.contact.faq_eyebrow')
                        ->label('FAQ Eyebrow'),
                    TextInput::make('pageContent.contact.faq_heading')
                        ->label('FAQ Heading'),
                ]),
            ]);
    }
}
