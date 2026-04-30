<?php

namespace App\Filament\Resources\Holidays\Tables;

use App\Filament\Actions\AuthorizedDeleteBulkAction;
use App\Filament\Actions\SlideOverEditAction;
use App\Models\Operations\Holiday;
use App\Models\Orders\Order;
use App\Presenters\HolidayPresenter;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class HolidaysTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(
                fn (Builder $query) => $query
                    ->addSelect([
                        'order_count' => Order::query()->selectRaw('count(*)')
                            ->whereColumn('delivery_date', 'holidays.date')
                            ->active(),
                    ]),
            )
            ->heading('Holidays')
            ->emptyStateHeading('No holidays planned')
            ->emptyStateDescription('Add upcoming holidays to track order deadlines.')
            ->emptyStateIcon(Heroicon::OutlinedCalendarDays)
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
                    ->getStateUsing(fn (Holiday $record) => HolidayPresenter::for($record)->orderingStatus())
                    ->color(fn (Holiday $record) => HolidayPresenter::for($record)->orderingStatusColor()),

                TextColumn::make('days_until')
                    ->label('Days Until')
                    ->getStateUsing(function (Holiday $record) {
                        if ($record->date->isPast()) {
                            return 'Passed';
                        }

                        return HolidayPresenter::for($record)->daysAway() . 'd';
                    }),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Active'),
            ])
            ->recordActions([
                SlideOverEditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    AuthorizedDeleteBulkAction::make(),
                ]),
            ]);
    }
}
