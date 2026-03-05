<?php

namespace App\Filament\Widgets;

use App\Models\OrderItem;
use Carbon\Carbon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\DB;

class PopularProductsWidget extends BaseWidget
{
    protected static ?int $sort = 4;

    protected static ?string $heading = 'Popular Products This Week';

    protected int|string|array $columnSpan = 'full';

    public function getTableRecordKey(\Illuminate\Database\Eloquent\Model|array $record): string
    {
        return $record->product_name;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                OrderItem::query()
                    ->join('orders', 'orders.id', '=', 'order_items.order_id')
                    ->where('orders.status', '!=', 'cancelled')
                    ->whereBetween('orders.requested_date', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
                    ->select(
                        'order_items.product_name',
                        DB::raw('SUM(order_items.quantity) as total_qty'),
                        DB::raw('SUM(order_items.line_total) as total_revenue')
                    )
                    ->groupBy('order_items.product_name')
                    ->orderByDesc('total_qty')
                    ->limit(5)
            )
            ->columns([
                TextColumn::make('product_name')
                    ->label('Product'),
                TextColumn::make('total_qty')
                    ->label('Qty')
                    ->badge(),
                TextColumn::make('total_revenue')
                    ->label('Revenue')
                    ->money('usd'),
            ])
            ->emptyStateHeading('No orders this week yet')
            ->emptyStateIcon('heroicon-o-cake');
    }
}
