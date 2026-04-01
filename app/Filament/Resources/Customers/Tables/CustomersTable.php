<?php

namespace App\Filament\Resources\Customers\Tables;

use App\Models\Customers\Customer;
use App\Services\Customer\BirthdayCalculator;
use App\Services\Customer\CustomerIntelligence;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Str;

class CustomersTable
{
    public static function configure(Table $table): Table
    {
        $atRiskDays = (int) (string) config('analytics.at_risk_threshold_days', 30);

        return $table
            ->modifyQueryUsing(fn (Builder $query) => resolve(CustomerIntelligence::class)->enrichQuery($query))
            ->columns([
                TextColumn::make('name')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('email')
                    ->sortable()
                    ->searchable()
                    ->copyable(),

                TextColumn::make('phone')
                    ->searchable()
                    ->copyable()
                    ->toggleable(),

                TextColumn::make('full_address')
                    ->label('Address')
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();

                        if (Str::length($state) <= $column->getCharacterLimit()) {
                            return null;
                        }

                        return $state;
                    }),

                TextColumn::make('birthday')
                    ->label('Birthday')
                    ->date('M j')
                    ->badge()
                    ->color(fn (Customer $record) => resolve(BirthdayCalculator::class)->isToday($record->birthday) ? 'success' : 'gray')
                    ->formatStateUsing(fn (mixed $state, Customer $record) => resolve(BirthdayCalculator::class)->isToday($record->birthday)
                        ? '🎂 Today!'
                        : ($state ? Date::parse($state)->format('M j') : '—'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('orders_sum_total')
                    ->label('Lifetime Value')
                    ->money('USD')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('orders_count')
                    ->label('Orders')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('last_order_date')
                    ->label('Last Order')
                    ->since()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('average_order_value')
                    ->label('Avg Order')
                    ->getStateUsing(fn (Customer $record) => $record->orders_count > 0
                        ? ($record->orders_sum_total / $record->orders_count)
                        : 0)
                    ->money('USD')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('is_at_risk')
                    ->label('Status')
                    ->badge()
                    ->getStateUsing(function (Customer $record) use ($atRiskDays) {
                        if ($record->orders_count > 0 && $record->last_order_date) {
                            return Date::parse($record->last_order_date)->diffInDays(now()) > $atRiskDays
                                ? 'At Risk'
                                : 'Active';
                        }

                        return 'Active';
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'At Risk' => 'danger',
                        default => 'success',
                    })
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('at_risk')
                    ->label('At Risk')
                    ->query(function (Builder $query) use ($atRiskDays) {
                        return $query->whereHas('orders')
                            ->whereDoesntHave('orders', fn (Builder $q) => $q->where('created_at', '>=', now()->subDays($atRiskDays)));
                    }),

                Filter::make('has_birthday_this_month')
                    ->label('Birthday This Month')
                    ->query(
                        fn (Builder $query) => $query->whereNotNull('birthday')
                            ->whereMonth('birthday', now()->month),
                    ),
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
            ->defaultSort('name')
            ->emptyStateHeading('No customers yet')
            ->emptyStateDescription('Customers will appear here once they place their first order.');
    }
}
