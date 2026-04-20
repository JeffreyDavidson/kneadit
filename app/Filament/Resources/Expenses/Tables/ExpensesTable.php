<?php

namespace App\Filament\Resources\Expenses\Tables;

use App\Enums\Financial\ExpenseCategory;
use App\Filament\Actions\SlideOverEditAction;
use App\Filament\Filters\AmountRangeFilter;
use App\Filament\Filters\DateRangeFilter;
use App\Filament\Tables\Columns\MoneyColumn;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ExpensesTable
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

                TextColumn::make('category')
                    ->badge(),

                MoneyColumn::make('amount')
                    ->sortable(),

                TextColumn::make('business_percentage')
                    ->label('Business %')
                    ->formatStateUsing(fn (\App\ValueObjects\Percentage $state) => $state->formatted())
                    ->sortable(),

                MoneyColumn::make('deductible_amount')
                    ->label('Deductible')
                    ->sortable(),

                ImageColumn::make('receipt_image')
                    ->label('Receipt')
                    ->height(50)
                    ->width(50),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->options(ExpenseCategory::class),

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
            ->emptyStateHeading('No expenses found')
            ->emptyStateDescription('Start tracking your business expenses by creating your first expense entry.');
    }
}
