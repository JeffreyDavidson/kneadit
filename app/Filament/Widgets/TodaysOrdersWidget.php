<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Carbon\Carbon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class TodaysOrdersWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected static ?string $heading = "Today's Orders";

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Order::query()
                    ->whereDate('requested_date', Carbon::today())
                    ->orderBy('requested_time')
            )
            ->columns([
                TextColumn::make('order_number')
                    ->label('Order #')
                    ->searchable(),
                TextColumn::make('customer_name')
                    ->label('Customer'),
                TextColumn::make('fulfillment_type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => ucfirst($state)),
                TextColumn::make('requested_time')
                    ->label('Time')
                    ->time('g:i A'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'pending' => 'warning',
                        'confirmed' => 'info',
                        'baking' => 'primary',
                        'ready' => 'success',
                        'delivered' => 'gray',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => ucfirst($state)),
                TextColumn::make('total')
                    ->money('usd'),
            ])
            ->emptyStateHeading("No orders today — enjoy the quiet! 🍞")
            ->emptyStateDescription('Orders scheduled for today will appear here.')
            ->emptyStateIcon('heroicon-o-shopping-bag');
    }
}
