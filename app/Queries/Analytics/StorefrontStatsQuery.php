<?php

namespace App\Queries\Analytics;

use App\Models\Customers\Customer;
use App\Models\Engagement\Review;
use App\Models\Orders\Order;
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
        $tenantKey = tenant()?->getTenantKey() ?? 'none';

        return Cache::flexible("storefront_stats_{$tenantKey}", [3600, 7200], fn () => [
            'customer_count' => Customer::query()->count(),
            'avg_rating' => (float) Review::query()->approved()->avg('rating'),
            'order_count' => Order::query()->count(),
        ]);
    }
}
