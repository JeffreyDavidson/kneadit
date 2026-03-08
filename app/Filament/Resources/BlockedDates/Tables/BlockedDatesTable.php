<?php

namespace App\Filament\Resources\BlockedDates\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\BooleanColumn;
use Filament\Tables\Columns\TextColumn;
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
                    ->visible(fn ($record) => $record && !$record->is_all_day),

                TextColumn::make('close_time')
                    ->time()
                    ->placeholder('—'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('date', 'asc');
    }
}
