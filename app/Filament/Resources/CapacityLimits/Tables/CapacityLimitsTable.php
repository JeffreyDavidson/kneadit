<?php

namespace App\Filament\Resources\CapacityLimits\Tables;

use App\Enums\Staff\DayOfWeek;
use App\Filament\Actions\AuthorizedDeleteBulkAction;
use App\Filament\Actions\SlideOverEditAction;
use App\Models\Operations\CapacityLimit;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
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
            ->emptyStateIcon(Heroicon::OutlinedClock)
            ->columns([
                TextColumn::make('day_label')
                    ->label('Day / Date')
                    ->sortable(
                        query: function (Builder $query, string $direction): Builder {
                            $sortDirection = $direction === 'desc' ? 'desc' : 'asc';

                            return $query
                                ->orderBy('specific_date', $sortDirection)
                                ->orderBy('day_of_week', $sortDirection);
                        },
                    )
                    ->getStateUsing(function (CapacityLimit $record) {
                        if ($record->specific_date) {
                            return $record->specific_date->format('D, M j, Y');
                        }

                        $day = DayOfWeek::tryFrom($record->day_of_week ?? '');

                        return $day?->getLabel() ?? '—';
                    }),

                TextColumn::make('max_orders')
                    ->label('Max Orders')
                    ->formatStateUsing(fn (int $state) => $state > 0 ? $state : 'Unlimited')
                    ->sortable(),

                IconColumn::make('is_blocked')
                    ->label('Blocked')
                    ->boolean()
                    ->trueIcon(Heroicon::OutlinedXCircle)
                    ->falseIcon(Heroicon::OutlinedCheckCircle)
                    ->trueColor('danger')
                    ->falseColor('success'),

                TextColumn::make('notes')
                    ->searchable()
                    ->limit(40)
                    ->placeholder('—')
                    ->toggleable(),
            ])
            ->defaultSort('day_of_week')
            ->filters([
                TernaryFilter::make('is_blocked')
                    ->label('Blocked'),
            ])
            ->recordActions([
                SlideOverEditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    AuthorizedDeleteBulkAction::make(),
                ]),
            ]);
    }
}
