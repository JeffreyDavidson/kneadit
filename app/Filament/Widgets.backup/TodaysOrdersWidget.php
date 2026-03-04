<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class TodaysOrdersWidget extends BaseWidget
{
    protected string $heading = "Today's Orders";
    
    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Order::query()
                    ->with(['customer'])
                    ->whereDate('requested_date', today())
                    ->orWhere(function ($query) {
                        $query->whereDate('created_at', today())
                              ->whereNull('requested_date');
                    })
                    ->latest()
            )
            ->columns([
                Tables\Columns\TextColumn::make('order_number')
                    ->label('Order #')
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable(),
                    
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'gray' => 'pending',
                        'warning' => 'confirmed',
                        'info' => 'baking',
                        'success' => 'ready',
                        'primary' => 'delivered',
                        'danger' => 'cancelled',
                    ]),
                    
                Tables\Columns\TextColumn::make('total')
                    ->money('USD'),
                    
                Tables\Columns\TextColumn::make('requested_time')
                    ->label('Time')
                    ->time(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->url(fn (Order $record): string => route('filament.admin.resources.orders.edit', $record)),
            ])
            ->emptyStateHeading('No orders for today')
            ->emptyStateDescription('Orders for today will appear here.')
            ->paginated(false);
    }
}