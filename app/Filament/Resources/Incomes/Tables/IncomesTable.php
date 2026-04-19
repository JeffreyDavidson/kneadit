<?php

namespace App\Filament\Resources\Incomes\Tables;

use App\Enums\Financial\IncomeSource;
use App\Filament\Actions\SlideOverEditAction;
use App\Filament\Filters\AmountRangeFilter;
use App\Filament\Filters\DateRangeFilter;
use App\Filament\Tables\Columns\MoneyColumn;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class IncomesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('date')
                    ->date()
                    ->sortable(),

                TextColumn::make('description')
                    ->searchable()
                    ->limit(50),

                TextColumn::make('source')
                    ->badge(),

                MoneyColumn::make('amount')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('source')
                    ->options(IncomeSource::class),

                DateRangeFilter::make('date'),

                AmountRangeFilter::make('amount'),
            ])
            ->recordActions([
                SlideOverEditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('date', 'desc')
            ->emptyStateHeading('No income found')
            ->emptyStateDescription('Start tracking your business income by creating your first income entry.');
    }
}
