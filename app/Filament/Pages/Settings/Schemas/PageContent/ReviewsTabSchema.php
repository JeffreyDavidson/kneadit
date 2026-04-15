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
                Grid::make(2)->schema([
                    TextInput::make('pageContent.reviews.hero_eyebrow')
                        ->label('Hero Eyebrow'),
                    TextInput::make('pageContent.reviews.hero_title')
                        ->label('Hero Title'),
                ]),
                Grid::make(2)->schema([
                    TextInput::make('pageContent.reviews.rating_eyebrow')
                        ->label('Rating Breakdown Eyebrow'),
                    TextInput::make('pageContent.reviews.all_reviews_label')
                        ->label('All Reviews Label'),
                ]),
                Grid::make(2)->schema([
                    TextInput::make('pageContent.reviews.empty_heading')
                        ->label('Empty State Heading'),
                    TextInput::make('pageContent.reviews.empty_description')
                        ->label('Empty State Description'),
                ]),
                Section::make('Call to Action')->schema([
                    Grid::make(2)->schema([
                        TextInput::make('pageContent.reviews.cta_script')
                            ->label('CTA Script Text'),
                        TextInput::make('pageContent.reviews.cta_button')
                            ->label('CTA Button Text'),
                    ]),
                    TextInput::make('pageContent.reviews.cta_heading')
                        ->label('CTA Heading'),
                    TextInput::make('pageContent.reviews.cta_description')
                        ->label('CTA Description'),
                ])->compact(),
            ]);
    }
}
