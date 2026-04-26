<?php

namespace App\Filament\Widgets;

use App\Models\Orders\Order;
use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Date;

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
                    ->whereDate('delivery_date', Date::today())
                    ->orderBy('delivery_time'),
            )
            ->columns([
                TextColumn::make('order_number')
                    ->label('Order #'),
                TextColumn::make('customer_name')
                    ->label('Customer'),
                TextColumn::make('delivery_type')
                    ->label('Type')
                    ->badge(),
                TextColumn::make('delivery_time')
                    ->label('Time')
                    ->time('g:i A'),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('total')
                    ->money('usd'),
            ])
            ->emptyStateHeading('No orders today — enjoy the quiet!')
            ->emptyStateDescription('Orders scheduled for today will appear here.')
            ->emptyStateIcon(Heroicon::OutlinedShoppingBag)
            ->headerActions([
                Action::make('viewAll')
                    ->label('View all')
                    ->url(route('filament.admin.resources.orders.index'))
                    ->view('filament.actions.view-all-link'),
            ]);
    }
}
