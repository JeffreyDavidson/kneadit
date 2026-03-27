<?php

namespace App\Filament\Resources\Incomes\Tables;

use App\Enums\IncomeSource;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

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

                BadgeColumn::make('source')
                    ->formatStateUsing(fn (mixed $state) => $state instanceof IncomeSource ? $state->label() : (IncomeSource::tryFrom($state)?->label() ?? $state))
                    ->colors([
                        'success' => 'farmers_market',
                        'primary' => 'cash_sale',
                        'info' => 'paypal_direct',
                        'warning' => 'catering',
                        'gray' => 'other',
                    ]),

                TextColumn::make('amount')
                    ->money('USD')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('source')
                    ->options(IncomeSource::class),

                Filter::make('date')
                    ->form([
                        DatePicker::make('from'),
                        DatePicker::make('until'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, string $date) => $query->whereDate('date', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, string $date) => $query->whereDate('date', '<=', $date),
                            );
                    }),

                Filter::make('amount')
                    ->form([
                        TextInput::make('min_amount')->numeric()->prefix('$'),
                        TextInput::make('max_amount')->numeric()->prefix('$'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query
                            ->when(
                                $data['min_amount'],
                                fn (Builder $query, string $amount) => $query->where('amount', '>=', $amount),
                            )
                            ->when(
                                $data['max_amount'],
                                fn (Builder $query, string $amount) => $query->where('amount', '<=', $amount),
                            );
                    }),
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
            ->defaultSort('date', 'desc')
            ->emptyStateHeading('No income found')
            ->emptyStateDescription('Start tracking your business income by creating your first income entry.');
    }
}
