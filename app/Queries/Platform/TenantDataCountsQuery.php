<?php

namespace App\Queries\Platform;

use App\Models\Platform\Tenant;
use App\Services\Tenants\TenancyManager;
use Illuminate\Support\Facades\DB;

class TenantDataCountsQuery
{
    public function __construct(private readonly TenancyManager $tenancyManager) {}

    /**
     * @return array{products: int, categories: int, orders: int, customers: int, reviews: int}
     */
    public function forTenant(Tenant $tenant): array
    {
        /** @var array{products: int, categories: int, orders: int, customers: int, reviews: int} $counts */
        $counts = $this->tenancyManager->withinTenant($tenant, fn (): array => [
            'products' => DB::table('products')->count(),
            'categories' => DB::table('categories')->count(),
            'orders' => DB::table('orders')->count(),
            'customers' => DB::table('users')->count(),
            'reviews' => DB::table('reviews')->count(),
        ]);

        return $counts;
    }
}
