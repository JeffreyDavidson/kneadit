<?php

namespace App\Queries\Reporting;

use App\Models\Orders\Order;
use App\Models\Orders\OrderItem;
use App\ValueObjects\DateRange;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

final class WeeklyDigestQuery
{
    /**
     * Ranks products by units sold on the revenue orders (paid, not cancelled)
     * delivered in the range.
     *
     * @return Collection<int, OrderItem>
     */
    public static function topProducts(DateRange $range, int $limit = 5): Collection
    {
        return OrderItem::query()
            ->select('product_id', DB::raw('SUM(quantity) as total_qty'))
            ->whereIn('order_id', Order::query()->revenueInDateRange($range)->select('id'))
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->limit($limit)
            ->with('product')
            ->get();
    }
}
