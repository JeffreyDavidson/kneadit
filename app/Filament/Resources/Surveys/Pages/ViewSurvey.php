<?php

namespace App\Filament\Resources\Surveys\Pages;

use App\Filament\Resources\Surveys\SurveyResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\ViewEntry;

class ViewSurvey extends ViewRecord
{
    protected static string $resource = SurveyResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Section::make('Survey Details')
                ->schema([
                    TextEntry::make('title'),
                    TextEntry::make('description'),
                    IconEntry::make('is_active')->boolean()->label('Active'),
                    TextEntry::make('responses_count')->label('Total Responses'),
                ]),

            Section::make('Results')
                ->schema([
                    ViewEntry::make('results')
                        ->view('filament.survey-results')
                        ->columnSpanFull(),
                ]),
        ]);
    }
}
