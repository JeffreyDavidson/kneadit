<?php

namespace App\Queries\Financial;

use App\Models\Orders\Order;
use App\Models\Orders\OrderItem;
use App\ValueObjects\DateRange;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class ProductSalesQuery
{
    /**
     * Get top products by revenue within a date range.
     *
     * @param DateRange|array<int, string> $range
     * @return Collection<int, array{name: string, units_sold: int, revenue: float}>
     */
    public static function topByRevenue(DateRange|array $range, int $limit = 10): Collection
    {
        $dates = $range instanceof DateRange ? $range->toArray() : $range;

        $orderIds = Order::query()->active()->whereBetween('delivery_date', $dates)->select('id');

        // unit_price is bigint cents (migration 2026_04_22_201500), so the
        // SUM(quantity * unit_price) aggregate returns cents — divide back to dollars.
        return OrderItem::query()
            ->whereIn('order_id', $orderIds)
            ->selectRaw('product_id, SUM(quantity) as units_sold, SUM(quantity * unit_price) as revenue_cents')
            ->groupBy('product_id')
            ->with('product:id,name')
            ->orderByDesc('revenue_cents')
            ->limit($limit)
            ->get()
            ->map(fn (OrderItem $item) => [
                'name' => $item->product->name ?? 'Deleted Product',
                'units_sold' => Arr::integer($item->getAttributes(), 'units_sold', 0),
                'revenue' => Arr::integer($item->getAttributes(), 'revenue_cents', 0) / 100.0,
            ]);
    }

    /**
     * Get top products by quantity sold.
     *
     * @param DateRange|array<int, string> $range
     * @return Collection<int, array{name: string, units_sold: int, revenue: float}>
     */
    public static function topByQuantity(DateRange|array $range, int $limit = 10): Collection
    {
        $dates = $range instanceof DateRange ? $range->toArray() : $range;

        $orderIds = Order::query()->active()->whereBetween('delivery_date', $dates)->select('id');

        // unit_price is bigint cents (migration 2026_04_22_201500); divide aggregates.
        return OrderItem::query()
            ->whereIn('order_id', $orderIds)
            ->selectRaw('product_id, SUM(quantity) as units_sold, SUM(quantity * unit_price) as revenue_cents')
            ->groupBy('product_id')
            ->with('product:id,name')
            ->orderByDesc('units_sold')
            ->limit($limit)
            ->get()
            ->map(fn (OrderItem $item) => [
                'name' => $item->product->name ?? 'Deleted Product',
                'units_sold' => Arr::integer($item->getAttributes(), 'units_sold', 0),
                'revenue' => Arr::integer($item->getAttributes(), 'revenue_cents', 0) / 100.0,
            ]);
    }
}
