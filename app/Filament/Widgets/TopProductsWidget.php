<?php

namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
use App\Models\OrderItem;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;

class TopProductsWidget extends ChartWidget
{
    protected static ?int $sort = 7;

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
            ->where('orders.status', '!=', OrderStatus::Cancelled)
            ->whereBetween('orders.delivery_date', [Date::now()->startOfMonth(), Date::now()->endOfMonth()])
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
                    'data' => $products->pluck('revenue')->map(fn (mixed $v) => (float) $v)->all(),
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
