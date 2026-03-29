<?php

namespace App\Filament\Resources\Expenses\Tables;

use App\Enums\ExpenseCategory;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

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
                    ->formatStateUsing(fn (mixed $state) => $state instanceof ExpenseCategory ? $state->getLabel() : (ExpenseCategory::tryFrom($state)?->getLabel() ?? $state))
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
                    ->formatStateUsing(fn (int $state) => $state . '%')
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
                    ->options(ExpenseCategory::class),

                Filter::make('date')
                    ->schema([
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
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['from'] ?? null) {
                            $indicators[] = 'From ' . \Carbon\Carbon::parse($data['from'])->toFormattedDateString();
                        }
                        if ($data['until'] ?? null) {
                            $indicators[] = 'Until ' . \Carbon\Carbon::parse($data['until'])->toFormattedDateString();
                        }

                        return $indicators;
                    }),

                Filter::make('amount')
                    ->schema([
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
            ->emptyStateHeading('No expenses found')
            ->emptyStateDescription('Start tracking your business expenses by creating your first expense entry.');
    }
}
