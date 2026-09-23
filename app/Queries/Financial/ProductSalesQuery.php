<?php

namespace App\Queries\Financial;

use App\Models\Orders\Order;
use App\Models\Orders\OrderItem;
use App\ValueObjects\DateRange;
use App\ValueObjects\Money;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class ProductSalesQuery
{
    /**
     * Get top products by revenue within a date range.
     *
     * @param  DateRange|array<int, string>  $range
     * @return Collection<int, array{name: string, units_sold: int, revenue: Money}>
     */
    public static function topByRevenue(DateRange|array $range, int $limit = 10): Collection
    {
        return self::aggregates($range)
            ->with('product:id,name')
            ->orderByDesc('revenue_cents')
            ->limit($limit)
            ->get()
            ->map(fn (OrderItem $item): array => [
                'name' => $item->product->name ?? 'Deleted Product',
                'units_sold' => Arr::integer($item->getAttributes(), 'units_sold', 0),
                'revenue' => Money::fromCents(Arr::integer($item->getAttributes(), 'revenue_cents', 0)),
            ]);
    }

    /**
     * Get top products by quantity sold.
     *
     * @param  DateRange|array<int, string>  $range
     * @return Collection<int, array{name: string, units_sold: int, revenue: Money}>
     */
    public static function topByQuantity(DateRange|array $range, int $limit = 10): Collection
    {
        return self::aggregates($range)
            ->with('product:id,name')
            ->orderByDesc('units_sold')
            ->limit($limit)
            ->get()
            ->map(fn (OrderItem $item): array => [
                'name' => $item->product->name ?? 'Deleted Product',
                'units_sold' => Arr::integer($item->getAttributes(), 'units_sold', 0),
                'revenue' => Money::fromCents(Arr::integer($item->getAttributes(), 'revenue_cents', 0)),
            ]);
    }

    /**
     * Build the shared paid product-sales aggregate used by the rankings.
     *
     * @param  DateRange|array<int, string>  $range
     * @return Builder<OrderItem>
     */
    public static function aggregates(DateRange|array $range): Builder
    {
        $dates = $range instanceof DateRange ? $range->toArray() : $range;
        $orderIds = Order::query()->active()->paid()->whereBetween('delivery_date', $dates)->select('id');

        // unit_price is bigint cents (migration 2026_04_22_201500), so the
        // SUM(quantity * unit_price) aggregate returns cents.
        return OrderItem::query()
            ->whereIn('order_id', $orderIds)
            ->selectRaw('product_id, SUM(quantity) as units_sold, SUM(quantity * unit_price) as revenue_cents')
            ->groupBy('product_id');
    }
}
