<?php

namespace App\Queries\Platform;

use App\DataTransferObjects\Platform\TenantOverviewMetrics;
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

    public function forTenant(Tenant $tenant): TenantOverviewMetrics
    {
        /** @var array{products: int, categories: int, orders: int, customers: int, reviews: int, revenue: float, last_order: string|null} $overview */
        $overview = $this->tenancyManager->withinTenant($tenant, fn (): array => [
            'products' => Product::query()->count(),
            'categories' => Category::query()->count(),
            'orders' => Order::query()->count(),
            // orders.total is bigint cents (migration 2026_04_22_201500).
            'revenue' => (float) ((int) Order::query()->sum('total') / 100),
            'customers' => Customer::query()->count(),
            'reviews' => Review::query()->count(),
            'last_order' => Order::query()->max('created_at'),
        ]);

        return new TenantOverviewMetrics(
            products: $overview['products'],
            categories: $overview['categories'],
            orders: $overview['orders'],
            customers: $overview['customers'],
            reviews: $overview['reviews'],
            revenue: $overview['revenue'],
            lastOrder: $overview['last_order'],
        );
    }
}
