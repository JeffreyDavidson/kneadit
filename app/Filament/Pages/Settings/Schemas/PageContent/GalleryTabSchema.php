<?php

namespace App\Filament\Pages\Settings\Schemas\PageContent;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs\Tab;

class GalleryTabSchema
{
    public static function make(): Tab
    {
        return Tab::make('Gallery')
            ->schema([
                Section::make('Hero')->schema([
                    Grid::make(2)->schema([
                        TextInput::make('pageContent.gallery.hero_eyebrow')
                            ->label('Eyebrow'),
                        TextInput::make('pageContent.gallery.hero_title')
                            ->label('Title'),
                    ]),
                    Textarea::make('pageContent.gallery.hero_subtitle')
                        ->label('Subtitle')
                        ->rows(2),
                ])->compact(),
                Section::make('Empty State')->schema([
                    TextInput::make('pageContent.gallery.empty_heading')
                        ->label('Heading'),
                    Textarea::make('pageContent.gallery.empty_description')
                        ->label('Description')
                        ->rows(2),
                    TextInput::make('pageContent.gallery.empty_script')
                        ->label('Script Text'),
                ])->compact(),
                Section::make('Upload')->schema([
                    Grid::make(3)->schema([
                        TextInput::make('pageContent.gallery.upload_eyebrow')
                            ->label('Eyebrow'),
                        TextInput::make('pageContent.gallery.upload_heading')
                            ->label('Heading'),
                        TextInput::make('pageContent.gallery.upload_description')
                            ->label('Description'),
                    ]),
                ])->compact(),
            ]);
    }
}
