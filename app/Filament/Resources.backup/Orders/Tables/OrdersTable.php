<?php

namespace App\Filament\Resources\Orders\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order_number')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('customer.name')
                    ->sortable()
                    ->searchable(),

                BadgeColumn::make('status')
                    ->colors([
                        'gray' => 'pending',
                        'warning' => 'confirmed',
                        'info' => 'baking',
                        'success' => 'ready',
                        'primary' => 'delivered',
                        'danger' => 'cancelled',
                    ]),

                BadgeColumn::make('payment_status')
                    ->label('Payment')
                    ->colors([
                        'danger' => 'unpaid',
                        'success' => 'paid',
                        'gray' => 'cancelled',
                        'warning' => 'refunded',
                    ]),

                TextColumn::make('total')
                    ->money('USD')
                    ->sortable(),

                TextColumn::make('requested_date')
                    ->date()
                    ->sortable(),

                TextColumn::make('user.name')
                    ->label('Baker')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'confirmed' => 'Confirmed',
                        'baking' => 'Baking',
                        'ready' => 'Ready',
                        'delivered' => 'Delivered',
                        'cancelled' => 'Cancelled',
                    ]),

                SelectFilter::make('payment_status')
                    ->options([
                        'unpaid' => 'Unpaid',
                        'paid' => 'Paid',
                        'cancelled' => 'Cancelled',
                        'refunded' => 'Refunded',
                    ]),

                Filter::make('requested_date')
                    ->form([
                        DatePicker::make('from'),
                        DatePicker::make('until'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when(
                                $data['from'],
                                fn ($query, $date) => $query->whereDate('requested_date', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn ($query, $date) => $query->whereDate('requested_date', '<=', $date),
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
            ->defaultSort('created_at', 'desc');
    }
}
