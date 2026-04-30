<?php

namespace App\Filament\Resources\BlockedDates\Tables;

use App\Enums\Operations\BlockedDateReason;
use App\Filament\Actions\AuthorizedDeleteBulkAction;
use App\Filament\Actions\SlideOverEditAction;
use App\Models\Operations\BlockedDate;
use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\IconColumn;
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
                    ->color(fn (?string $state): string => BlockedDateReason::tryFrom($state ?? '')?->getColor() ?? 'gray')
                    ->placeholder('No reason'),

                IconColumn::make('is_all_day')
                    ->boolean()
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
                SlideOverEditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    AuthorizedDeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('date', 'asc')
            ->emptyStateHeading('No blocked dates')
            ->emptyStateDescription('Block specific dates to prevent orders.');
    }
}
