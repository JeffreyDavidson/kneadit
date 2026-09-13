<?php

namespace App\Queries\Platform;

use App\Models\Platform\Tenant;

final class TenantStatsQuery
{
    public function __construct(private readonly TenantOverviewQuery $overviewQuery) {}

    /**
     * @return array{products: int, orders: int, revenue: float, customers: int, reviews: int, last_order: string|null}
     */
    public function forTenant(Tenant $tenant): array
    {
        $overview = $this->overviewQuery->forTenant($tenant);

        return [
            'products' => $overview['products'],
            'orders' => $overview['orders'],
            'revenue' => $overview['revenue'],
            'customers' => $overview['customers'],
            'reviews' => $overview['reviews'],
            'last_order' => $overview['last_order'],
        ];
    }
}
