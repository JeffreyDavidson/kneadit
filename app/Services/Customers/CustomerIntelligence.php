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
        private readonly CustomerLoyalty $customerLoyalty,
    ) {}

    public function metrics(Customer $customer): CustomerMetrics
    {
        // 1 query: activity aggregates (every order that is not cancelled)
        $orderStats = $customer->orders()->active()
            ->selectRaw('count(*) as order_count, max(created_at) as last_order_date')
            ->first();

        // 1 query: spend aggregates (revenue orders only: paid, not cancelled)
        $revenueStats = $customer->orders()->revenue()
            ->selectRaw('count(*) as revenue_order_count, coalesce(sum(total), 0) as lifetime_value')
            ->first();

        $orderCount = Arr::integer(['value' => $orderStats->order_count ?? 0], 'value', 0);
        $revenueOrderCount = Arr::integer(['value' => $revenueStats->revenue_order_count ?? 0], 'value', 0);
        // orders.total is bigint cents (migration 2026_04_22_201500).
        $lifetimeValue = Money::fromCents(Arr::integer(['value' => $revenueStats->lifetime_value ?? 0], 'value', 0));
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
            averageOrderValue: $revenueOrderCount > 0
                ? $lifetimeValue->multiply(1 / $revenueOrderCount)
                : Money::zero(),
            lastOrderDate: $lastOrderDate,
            daysSinceLastOrder: $daysSinceLastOrder,
            isAtRisk: $isAtRisk,
            totalPoints: $balance->total,
            lifetimePointsEarned: $balance->earned,
        );
    }
}
