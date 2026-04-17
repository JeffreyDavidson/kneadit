<?php

namespace App\Filament\Resources\Surveys\Tables;

use App\Filament\Actions\SlideOverEditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class SurveysTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->sortable()
                    ->searchable(),

                IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active'),

                TextColumn::make('responses_count')
                    ->label('Responses')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Active'),
            ])
            ->recordActions([
                ViewAction::make(),
                SlideOverEditAction::make(),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('No surveys yet')
            ->emptyStateDescription('Create a survey to gather customer feedback.');
    }
}
