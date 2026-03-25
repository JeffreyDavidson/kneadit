<?php

namespace App\Filament\Resources\CapacityLimits\Tables;

use App\Enums\DayOfWeek;
use App\Models\CapacityLimit;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CapacityLimitsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->heading('Capacity Limits')
            ->emptyStateHeading('No capacity limits set')
            ->emptyStateDescription('All days are open for orders.')
            ->emptyStateIcon('heroicon-o-clock')
            ->columns([
                TextColumn::make('day_label')
                    ->label('Day / Date')
                    ->sortable(query: fn (Builder $query, string $direction) => $query
                        ->orderByRaw('COALESCE(specific_date, day_of_week) '.($direction === 'desc' ? 'desc' : 'asc'))
                    )
                    ->getStateUsing(function (CapacityLimit $record) {
                        if ($record->specific_date) {
                            return $record->specific_date->format('D, M j, Y');
                        }

                        $day = DayOfWeek::tryFrom($record->day_of_week);

                        return $day?->label() ?? '—';
                    }),

                TextColumn::make('max_orders')
                    ->label('Max Orders')
                    ->formatStateUsing(fn (int $state) => $state > 0 ? $state : 'Unlimited')
                    ->sortable(),

                IconColumn::make('is_blocked')
                    ->label('Blocked')
                    ->boolean()
                    ->trueIcon('heroicon-o-x-circle')
                    ->falseIcon('heroicon-o-check-circle')
                    ->trueColor('danger')
                    ->falseColor('success'),

                TextColumn::make('notes')
                    ->limit(40)
                    ->placeholder('—')
                    ->toggleable(),
            ])
            ->defaultSort('day_of_week')
            ->recordActions([
                EditAction::make()->slideOver()->modalWidth('md'),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
