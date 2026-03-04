<?php

namespace App\Filament\Widgets;

use App\Models\OrderItem;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class PopularProductsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        // Get top 5 products by order count in the last 30 days
        $topProducts = OrderItem::select(
                'product_id',
                'products.name as product_name',
                DB::raw('SUM(order_items.quantity) as total_quantity'),
                DB::raw('COUNT(DISTINCT order_items.order_id) as order_count')
            )
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.created_at', '>=', now()->subDays(30))
            ->where('orders.status', '!=', 'cancelled')
            ->groupBy('product_id', 'products.name')
            ->orderByDesc('order_count')
            ->limit(5)
            ->get();

        $stats = [];
        
        foreach ($topProducts as $index => $product) {
            $stats[] = Stat::make(
                '#' . ($index + 1) . ' ' . $product->product_name,
                $product->order_count . ' orders'
            )
                ->description($product->total_quantity . ' units sold')
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color($index === 0 ? 'success' : ($index === 1 ? 'warning' : 'primary'));
        }
        
        // Fill remaining slots if less than 5 products
        while (count($stats) < 5) {
            $stats[] = Stat::make(
                'No data',
                '0 orders'
            )
                ->description('Not enough data')
                ->color('gray');
        }

        return $stats;
    }
    
    protected function getHeading(): ?string
    {
        return 'Popular Products (Last 30 Days)';
    }
}