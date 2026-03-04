<?php

namespace App\Filament\Resources\Expenses\Tables;

use App\Models\Expense;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;

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

                BadgeColumn::make('category')
                    ->formatStateUsing(fn ($state) => Expense::CATEGORIES[$state] ?? $state)
                    ->colors([
                        'primary' => 'ingredients',
                        'success' => 'packaging',
                        'warning' => 'equipment',
                        'info' => 'delivery',
                        'gray' => 'other',
                    ]),

                TextColumn::make('amount')
                    ->money('USD')
                    ->sortable(),

                TextColumn::make('business_percentage')
                    ->label('Business %')
                    ->formatStateUsing(fn ($state) => $state . '%')
                    ->sortable(),

                TextColumn::make('deductible_amount')
                    ->label('Deductible')
                    ->money('USD')
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
                    ->options(Expense::CATEGORIES),

                Filter::make('date')
                    ->form([
                        DatePicker::make('from'),
                        DatePicker::make('until'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when(
                                $data['from'],
                                fn ($query, $date) => $query->whereDate('date', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn ($query, $date) => $query->whereDate('date', '<=', $date),
                            );
                    }),

                Filter::make('amount')
                    ->form([
                        TextInput::make('min_amount')->numeric()->prefix('$'),
                        TextInput::make('max_amount')->numeric()->prefix('$'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when(
                                $data['min_amount'],
                                fn ($query, $amount) => $query->where('amount', '>=', $amount),
                            )
                            ->when(
                                $data['max_amount'],
                                fn ($query, $amount) => $query->where('amount', '<=', $amount),
                            );
                    }),
            ])
            ->recordActions([
                EditAction::make(),
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