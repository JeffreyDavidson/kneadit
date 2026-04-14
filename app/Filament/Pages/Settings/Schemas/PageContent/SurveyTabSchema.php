<?php

namespace App\Filament\Pages\Settings\Schemas\PageContent;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs\Tab;

class SurveyTabSchema
{
    public static function make(): Tab
    {
        return Tab::make('Survey')
            ->schema([
                TextInput::make('pageContent.survey.hero_eyebrow')
                    ->label('Hero Eyebrow'),
                Grid::make(2)->schema([
                    TextInput::make('pageContent.survey.submit_button')
                        ->label('Submit Button Text'),
                    TextInput::make('pageContent.survey.submit_footer')
                        ->label('Submit Footer Text'),
                ]),
                Section::make('Success State')->schema([
                    Grid::make(2)->schema([
                        TextInput::make('pageContent.survey.success_title')
                            ->label('Title'),
                        TextInput::make('pageContent.survey.success_description')
                            ->label('Description'),
                    ]),
                ])->compact(),
            ]);
    }
}
