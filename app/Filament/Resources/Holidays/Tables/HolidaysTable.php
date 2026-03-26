<?php

namespace App\Filament\Resources\Holidays\Tables;

use App\Enums\OrderStatus;
use App\Models\Holiday;
use App\Models\Order;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Date;

class HolidaysTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query
                ->addSelect([
                    'order_count' => Order::query()->selectRaw('count(*)')
                        ->whereColumn('delivery_date', 'holidays.date')
                        ->where('status', '!=', OrderStatus::Cancelled),
                ]),
            )
            ->heading('Holidays')
            ->emptyStateHeading('No holidays planned')
            ->emptyStateDescription('Add upcoming holidays to track order deadlines.')
            ->emptyStateIcon('heroicon-o-calendar-days')
            ->defaultSort('date')
            ->columns([
                TextColumn::make('name')
                    ->label('Holiday')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('date')
                    ->label('Date')
                    ->date('D, M j, Y')
                    ->sortable(),

                TextColumn::make('order_deadline')
                    ->label('Deadline')
                    ->date('M j')
                    ->sortable(),

                TextColumn::make('orders_display')
                    ->label('Orders')
                    ->getStateUsing(function (Holiday $record) {
                        $count = $record->order_count ?? 0;

                        return $record->max_orders
                            ? "{$count} / {$record->max_orders}"
                            : (string) $count;
                    }),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->getStateUsing(function (Holiday $record) {
                        if ($record->date->isPast()) {
                            return 'Past';
                        }
                        if ($record->isDeadlinePassed()) {
                            return 'Deadline passed';
                        }
                        $days = $record->daysUntilDeadline();
                        if ($days <= 3) {
                            return 'Urgent';
                        }

                        return 'Open';
                    })
                    ->color(fn (string $state) => match ($state) {
                        'Past' => 'gray',
                        'Deadline passed' => 'danger',
                        'Urgent' => 'warning',
                        'Open' => 'success',
                        default => 'gray',
                    }),

                TextColumn::make('days_until')
                    ->label('Days Until')
                    ->getStateUsing(function (Holiday $record) {
                        if ($record->date->isPast()) {
                            return 'Passed';
                        }
                        $days = (int) Date::today()->diffInDays($record->date, false);

                        return "{$days}d";
                    }),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                EditAction::make()->slideOver()->modalWidth('md'),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
