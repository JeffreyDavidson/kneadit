<?php

namespace App\Filament\Pages\Settings\Schemas\PageContent;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs\Tab;

class ReviewsTabSchema
{
    public static function make(): Tab
    {
        return Tab::make('Reviews')
            ->schema([
                Section::make('Hero')->schema([
                    Grid::make(2)->schema([
                        TextInput::make('pageContent.reviews.hero_eyebrow')
                            ->label('Eyebrow'),
                        TextInput::make('pageContent.reviews.hero_title')
                            ->label('Title'),
                    ]),
                ])->compact(),
                Section::make('Reviews List')->schema([
                    Grid::make(2)->schema([
                        TextInput::make('pageContent.reviews.rating_eyebrow')
                            ->label('Rating Breakdown Eyebrow'),
                        TextInput::make('pageContent.reviews.all_reviews_label')
                            ->label('All Reviews Label'),
                    ]),
                ])->compact(),
                Section::make('Empty State')->schema([
                    Grid::make(2)->schema([
                        TextInput::make('pageContent.reviews.empty_heading')
                            ->label('Heading'),
                        TextInput::make('pageContent.reviews.empty_description')
                            ->label('Description'),
                    ]),
                ])->compact(),
                Section::make('Call to Action')->schema([
                    Grid::make(2)->schema([
                        TextInput::make('pageContent.reviews.cta_script')
                            ->label('Script Text'),
                        TextInput::make('pageContent.reviews.cta_button')
                            ->label('Button Text'),
                    ]),
                    TextInput::make('pageContent.reviews.cta_heading')
                        ->label('Heading'),
                    TextInput::make('pageContent.reviews.cta_description')
                        ->label('Description'),
                ])->compact(),
            ]);
    }
}
