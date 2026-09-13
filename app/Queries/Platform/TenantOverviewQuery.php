<?php

namespace App\Queries\Platform;

use App\Models\Platform\Tenant;
use App\Services\Tenants\TenancyManager;
use Illuminate\Support\Facades\DB;

final class TenantOverviewQuery
{
    public function __construct(private readonly TenancyManager $tenancyManager) {}

    /**
     * @return array{products: int, categories: int, orders: int, customers: int, reviews: int, revenue: float, last_order: string|null}
     */
    public function forTenant(Tenant $tenant): array
    {
        /** @var array{products: int, categories: int, orders: int, customers: int, reviews: int, revenue: float, last_order: string|null} $overview */
        $overview = $this->tenancyManager->withinTenant($tenant, fn (): array => [
            'products' => DB::table('products')->count(),
            'categories' => DB::table('categories')->count(),
            'orders' => DB::table('orders')->count(),
            // orders.total is bigint cents (migration 2026_04_22_201500).
            'revenue' => (float) ((int) DB::table('orders')->sum('total') / 100),
            'customers' => DB::table('customers')->count(),
            'reviews' => DB::table('reviews')->count(),
            'last_order' => DB::table('orders')->max('created_at'),
        ]);

        return $overview;
    }
}
