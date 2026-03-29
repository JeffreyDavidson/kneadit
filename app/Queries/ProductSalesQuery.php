<?php

namespace App\Queries;

use App\Enums\OrderStatus;
use App\Models\OrderItem;
use App\ValueObjects\DateRange;
use Illuminate\Support\Collection;

class ProductSalesQuery
{
    /**
     * Get top products by revenue within a date range.
     *
     * @return Collection<int, array{name: string, units_sold: int, revenue: float}>
     */
    public static function topByRevenue(DateRange|array $range, int $limit = 10): Collection
    {
        $dates = $range instanceof DateRange ? $range->toArray() : $range;

        return OrderItem::query()
            ->whereIn('order_id', function ($q) use ($dates) {
                $q->select('id')
                    ->from('orders')
                    ->whereNotIn('status', [OrderStatus::Cancelled->value])
                    ->whereBetween('delivery_date', $dates);
            })
            ->selectRaw('product_id, SUM(quantity) as units_sold, SUM(quantity * unit_price) as revenue')
            ->groupBy('product_id')
            ->with('product:id,name')
            ->orderByDesc('revenue')
            ->limit($limit)
            ->get()
            ->map(fn (OrderItem $item) => [
                'name' => $item->product->name ?? 'Deleted Product',
                'units_sold' => (int) $item->units_sold,
                'revenue' => (float) $item->revenue,
            ]);
    }

    /**
     * Get top products by quantity sold.
     *
     * @return Collection<int, array{name: string, units_sold: int, revenue: float}>
     */
    public static function topByQuantity(DateRange|array $range, int $limit = 10): Collection
    {
        $dates = $range instanceof DateRange ? $range->toArray() : $range;

        return OrderItem::query()
            ->whereIn('order_id', function ($q) use ($dates) {
                $q->select('id')
                    ->from('orders')
                    ->whereNotIn('status', [OrderStatus::Cancelled->value])
                    ->whereBetween('delivery_date', $dates);
            })
            ->selectRaw('product_id, SUM(quantity) as units_sold, SUM(quantity * unit_price) as revenue')
            ->groupBy('product_id')
            ->with('product:id,name')
            ->orderByDesc('units_sold')
            ->limit($limit)
            ->get()
            ->map(fn (OrderItem $item) => [
                'name' => $item->product->name ?? 'Deleted Product',
                'units_sold' => (int) $item->units_sold,
                'revenue' => (float) $item->revenue,
            ]);
    }
}
