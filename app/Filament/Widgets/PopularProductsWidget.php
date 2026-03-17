<?php

namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
use App\Models\OrderItem;
use Carbon\Carbon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PopularProductsWidget extends BaseWidget
{
    protected static ?int $sort = 4;

    protected static ?string $heading = 'Popular Products This Week';

    protected int|string|array $columnSpan = 'full';

    public function getTableRecordKey(Model|array $record): string
    {
        return (string) $record->product_id;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                OrderItem::query()
                    ->join('orders', 'orders.id', '=', 'order_items.order_id')
                    ->join('products', 'products.id', '=', 'order_items.product_id')
                    ->where('orders.status', '!=', OrderStatus::Cancelled)
                    ->whereBetween('orders.delivery_date', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
                    ->select(
                        'order_items.product_id',
                        'products.name as product_name',
                        DB::raw('SUM(order_items.quantity) as total_qty'),
                        DB::raw('SUM(order_items.quantity * order_items.unit_price) as total_revenue')
                    )
                    ->groupBy('order_items.product_id', 'products.name')
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
