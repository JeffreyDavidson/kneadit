<?php

namespace App\Queries;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Review;
use Illuminate\Support\Facades\Cache;

class StorefrontStatsQuery
{
    /**
     * Get storefront statistics (customer count, avg rating, order count).
     * Cached for 1-2 hours using stale-while-revalidate.
     *
     * @return array{customer_count: int, avg_rating: float, order_count: int}
     */
    public static function get(): array
    {
        return Cache::flexible('storefront_stats', [3600, 7200], fn () => [
            'customer_count' => Customer::query()->count(),
            'avg_rating' => (float) Review::query()->approved()->avg('rating'),
            'order_count' => Order::query()->count(),
        ]);
    }
}
