<?php

namespace App\DataTransferObjects\Reporting;

use App\Models\Orders\OrderItem;
use App\ValueObjects\Money;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;

final readonly class WeeklyDigestData
{
    /**
     * @param array{total_orders: int, total_revenue: Money, new_customers: int, avg_order_value: Money} $stats
     * @param Collection<int, OrderItem> $topProducts
     * @param SupportCollection<int, array{name: string, days_since_last_order: ?int}> $atRiskCustomers
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
