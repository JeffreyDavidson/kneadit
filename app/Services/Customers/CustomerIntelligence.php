<?php

namespace App\Services\Customers;

use App\DataTransferObjects\Customers\CustomerMetrics;
use App\Enums\Customers\CustomerStatus;
use App\Models\Customers\Customer;
use App\Services\Loyalty\CustomerLoyalty;
use App\ValueObjects\Money;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Date;

class CustomerIntelligence
{
    public function __construct(
        private CustomerLoyalty $customerLoyalty,
    ) {}

    public function metrics(Customer $customer): CustomerMetrics
    {
        // 1 query: order aggregates
        $orderStats = $customer->orders()->active()
            ->selectRaw('count(*) as order_count, coalesce(sum(total), 0) as lifetime_value, max(created_at) as last_order_date')
            ->first();

        $orderCount = Arr::integer(['value' => $orderStats->order_count ?? 0], 'value', 0);
        // orders.total is bigint cents (migration 2026_04_22_201500).
        $lifetimeValue = Money::fromCents(Arr::integer(['value' => $orderStats->lifetime_value ?? 0], 'value', 0));
        $lastOrderDate = $orderStats?->last_order_date ? Date::parse($orderStats->last_order_date) : null;

        $daysSinceLastOrder = $lastOrderDate
            ? (int) $lastOrderDate->diffInDays(now())
            : null;

        $isAtRisk = CustomerStatus::resolve($orderCount, $lastOrderDate) === CustomerStatus::AtRisk;

        // 1 query: loyalty point aggregates
        $balance = $this->customerLoyalty->balance($customer);

        return new CustomerMetrics(
            lifetimeValue: $lifetimeValue,
            orderCount: $orderCount,
            averageOrderValue: $orderCount > 0
                ? $lifetimeValue->multiply(1 / $orderCount)
                : Money::zero(),
            lastOrderDate: $lastOrderDate,
            daysSinceLastOrder: $daysSinceLastOrder,
            isAtRisk: $isAtRisk,
            totalPoints: $balance->total,
            lifetimePointsEarned: $balance->earned,
        );
    }
}
