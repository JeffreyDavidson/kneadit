<?php

namespace App\Filament\Resources\BlockedDateResource\Tables;

use App\Models\Operations\BlockedDate;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\BooleanColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class BlockedDatesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('date')
                    ->date()
                    ->sortable(),

                TextColumn::make('reason')
                    ->searchable()
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'Vacation' => 'info',
                        'Holiday' => 'warning',
                        'Maintenance' => 'danger',
                        default => 'gray',
                    })
                    ->placeholder('No reason'),

                BooleanColumn::make('is_all_day')
                    ->label('All Day'),

                TextColumn::make('open_time')
                    ->time()
                    ->placeholder('—')
                    ->visible(fn (?BlockedDate $record) => $record && ! $record->is_all_day),

                TextColumn::make('close_time')
                    ->time()
                    ->placeholder('—'),
            ])
            ->filters([
                TernaryFilter::make('is_all_day')
                    ->label('All Day'),
            ])
            ->recordActions([
                EditAction::make()
                    ->slideOver()
                    ->modalWidth('md'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('date', 'asc')
            ->emptyStateHeading('No blocked dates')
            ->emptyStateDescription('Block specific dates to prevent orders.');
    }
}
