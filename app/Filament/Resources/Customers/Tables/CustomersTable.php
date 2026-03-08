<?php

namespace App\Filament\Resources\Customers\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

class CustomersTable
{
    public static function configure(Table $table): Table
    {
        return $table
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

                        if (strlen($state) <= $column->getCharacterLimit()) {
                            return null;
                        }

                        return $state;
                    }),

                TextColumn::make('lifetime_value')
                    ->label('Lifetime Value')
                    ->money('USD')
                    ->sortable(query: function ($query, string $direction) {
                        return $query->withSum(['orders' => fn ($q) => $q->where('status', '!=', 'cancelled')], 'total')
                            ->orderBy('orders_sum_total', $direction);
                    })
                    ->toggleable(),

                TextColumn::make('order_count')
                    ->label('Orders')
                    ->sortable(query: function ($query, string $direction) {
                        return $query->withCount(['orders' => fn ($q) => $q->where('status', '!=', 'cancelled')])
                            ->orderBy('orders_count', $direction);
                    })
                    ->toggleable(),

                TextColumn::make('last_order_date')
                    ->label('Last Order')
                    ->since()
                    ->sortable(query: function ($query, string $direction) {
                        return $query->orderBy(
                            \App\Models\Order::select('created_at')
                                ->whereColumn('customer_id', 'customers.id')
                                ->latest()
                                ->limit(1),
                            $direction
                        );
                    })
                    ->toggleable(),

                TextColumn::make('average_order_value')
                    ->label('Avg Order')
                    ->money('USD')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('is_at_risk')
                    ->label('Status')
                    ->badge()
                    ->getStateUsing(fn ($record) => $record->is_at_risk ? 'At Risk' : 'Active')
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
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('name');
    }
}
