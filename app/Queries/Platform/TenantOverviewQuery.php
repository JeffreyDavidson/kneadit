<?php

namespace App\Queries\Platform;

use App\Models\Customers\Customer;
use App\Models\Engagement\Review;
use App\Models\Inventory\Category;
use App\Models\Inventory\Product;
use App\Models\Orders\Order;
use App\Models\Platform\Tenant;
use App\Services\Tenants\TenancyManager;

final readonly class TenantOverviewQuery
{
    public function __construct(private TenancyManager $tenancyManager) {}

    /** @return array{products: int, categories: int, orders: int, customers: int, reviews: int} */
    public function exportCounts(Tenant $tenant): array
    {
        return $this->tenancyManager->withinTenant($tenant, fn (): array => [
            'products' => Product::query()->count(),
            'categories' => Category::query()->count(),
            'orders' => Order::query()->count(),
            'customers' => Customer::query()->count(),
            'reviews' => Review::query()->count(),
        ]);
    }

    /** @return array{products: int, orders: int, revenue: float, customers: int, reviews: int, last_order: string|null} */
    public function adminStats(Tenant $tenant): array
    {
        return $this->tenancyManager->withinTenant($tenant, function (): array {
            $lastOrder = Order::query()->max('created_at');

            return [
                'products' => Product::query()->count(),
                'orders' => Order::query()->count(),
                // orders.total is bigint cents (migration 2026_04_22_201500).
                'revenue' => (float) ((int) Order::query()->sum('total') / 100),
                'customers' => Customer::query()->count(),
                'reviews' => Review::query()->count(),
                'last_order' => is_string($lastOrder) ? $lastOrder : null,
            ];
        });
    }
}
