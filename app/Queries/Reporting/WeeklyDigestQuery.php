<?php

namespace App\Queries\Reporting;

use App\Models\Orders\OrderItem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

final class WeeklyDigestQuery
{
    /** @return Collection<int, OrderItem> */
    public static function topProducts(Carbon $start, Carbon $end, int $limit = 5): Collection
    {
        return OrderItem::query()
            ->select('product_id', DB::raw('SUM(quantity) as total_qty'))
            ->whereHas('order', fn (Builder $query) => $query->whereBetween('created_at', [$start, $end]))
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->limit($limit)
            ->with('product')
            ->get();
    }
}
