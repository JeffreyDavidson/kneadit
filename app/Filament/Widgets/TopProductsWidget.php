<?php

namespace App\Filament\Widgets;

use App\Models\OrderItem;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class TopProductsWidget extends ChartWidget
{
    protected static ?int $sort = 6;

    protected ?string $heading = 'Top Products This Month';

    protected int|string|array $columnSpan = 1;

    protected ?string $maxHeight = '240px';

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $products = OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->where('orders.status', '!=', 'cancelled')
            ->whereBetween('orders.delivery_date', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])
            ->select(
                'products.name',
                DB::raw('SUM(order_items.quantity * order_items.unit_price) as revenue')
            )
            ->groupBy('products.name')
            ->orderByDesc('revenue')
            ->limit(5)
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Revenue ($)',
                    'data' => $products->pluck('revenue')->map(fn ($v) => (float) $v)->toArray(),
                    'backgroundColor' => ['#8B5E3C', '#D4A574', '#F5E6D3', '#A0522D', '#DEB887'],
                    'borderRadius' => 6,
                ],
            ],
            'labels' => $products->pluck('name')->toArray(),
        ];
    }

    protected function getOptions(): array
    {
        return [
            'indexAxis' => 'y',
            'plugins' => [
                'legend' => ['display' => false],
            ],
        ];
    }
}
