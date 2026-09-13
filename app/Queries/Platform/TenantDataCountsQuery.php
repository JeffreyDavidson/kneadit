<?php

namespace App\Queries\Platform;

use App\Models\Platform\Tenant;

class TenantDataCountsQuery
{
    public function __construct(private readonly TenantOverviewQuery $overviewQuery) {}

    /**
     * @return array{products: int, categories: int, orders: int, customers: int, reviews: int}
     */
    public function forTenant(Tenant $tenant): array
    {
        $overview = $this->overviewQuery->forTenant($tenant);

        return [
            'products' => $overview['products'],
            'categories' => $overview['categories'],
            'orders' => $overview['orders'],
            'customers' => $overview['customers'],
            'reviews' => $overview['reviews'],
        ];
    }
}
