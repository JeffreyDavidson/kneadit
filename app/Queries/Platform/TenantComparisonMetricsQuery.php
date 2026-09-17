<?php

namespace App\Queries\Platform;

use App\DataTransferObjects\Platform\TenantMetrics;
use App\Models\Engagement\Review;
use App\Models\Inventory\Category;
use App\Models\Inventory\Product;
use App\Models\Orders\Order;
use App\Models\Platform\Tenant;
use App\Services\Tenants\TenancyManager;

final readonly class TenantComparisonMetricsQuery
{
    public function __construct(private TenancyManager $tenancyManager) {}

    public function forTenant(Tenant $tenant): TenantMetrics
    {
        try {
            /** @var array{total_orders: int, month_orders: int, total_products: int, total_categories: int, avg_review: float|int|null} $metrics */
            $metrics = $this->tenancyManager->withinTenant($tenant, fn (): array => [
                'total_orders' => Order::query()->count(),
                'month_orders' => Order::query()
                    ->where('created_at', '>=', now()->startOfMonth())
                    ->count(),
                'total_products' => Product::query()->count(),
                'total_categories' => Category::query()->count(),
                'avg_review' => Review::query()->avg('rating'),
            ]);
        } catch (\Throwable) {
            $metrics = [];
        }

        return new TenantMetrics(
            id: $tenant->id,
            name: $tenant->store_name ?? $tenant->name,
            plan: $tenant->plan->value ?? 'trial',
            totalOrders: $metrics['total_orders'] ?? 0,
            monthOrders: $metrics['month_orders'] ?? 0,
            totalProducts: $metrics['total_products'] ?? 0,
            totalCategories: $metrics['total_categories'] ?? 0,
            avgReview: round((float) ($metrics['avg_review'] ?? 0), 1),
        );
    }
}
