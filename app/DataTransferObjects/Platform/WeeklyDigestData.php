<?php

namespace App\DataTransferObjects\Platform;

use App\Models\Orders\OrderItem;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;

final readonly class WeeklyDigestData
{
    /**
     * @param  array{total_orders: int, total_revenue: string, new_customers: int, avg_order_value: string}  $stats
     * @param  Collection<int, OrderItem>  $topProducts
     * @param  SupportCollection<int, array{name: string, days_since_last_order: ?int}>  $atRiskCustomers
     */
    public function __construct(
        public array $stats,
        public Collection $topProducts,
        public SupportCollection $atRiskCustomers,
        public int $upcomingCount,
        public string $storeName,
        public string $adminUrl,
    ) {}
}
