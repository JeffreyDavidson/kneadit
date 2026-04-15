<?php

namespace App\Filament\Pages\Settings\Schemas\PageContent;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs\Tab;

class SubmitReviewTabSchema
{
    public static function make(): Tab
    {
        return Tab::make('Submit Review')
            ->schema([
                Grid::make(2)->schema([
                    TextInput::make('pageContent.submit_review.hero_eyebrow')
                        ->label('Hero Eyebrow'),
                    TextInput::make('pageContent.submit_review.hero_title')
                        ->label('Hero Title'),
                ]),
                Grid::make(2)->schema([
                    TextInput::make('pageContent.submit_review.rating_label')
                        ->label('Rating Label'),
                    TextInput::make('pageContent.submit_review.submit_button')
                        ->label('Submit Button Text'),
                ]),
                Grid::make(2)->schema([
                    TextInput::make('pageContent.submit_review.comment_label')
                        ->label('Comment Label'),
                    TextInput::make('pageContent.submit_review.comment_placeholder')
                        ->label('Comment Placeholder'),
                ]),
                TextInput::make('pageContent.submit_review.photo_label')
                    ->label('Photo Upload Label'),
                Section::make('Success State')->schema([
                    Grid::make(2)->schema([
                        TextInput::make('pageContent.submit_review.success_title')
                            ->label('Title'),
                        TextInput::make('pageContent.submit_review.success_description')
                            ->label('Description'),
                    ]),
                ])->compact(),
            ]);
    }
}
