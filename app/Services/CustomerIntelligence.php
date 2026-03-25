<?php

namespace App\Services;

use App\DataTransferObjects\CustomerMetrics;
use App\Enums\LoyaltyPointType;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Setting;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Date;

class CustomerIntelligence
{
    public function metrics(Customer $customer): CustomerMetrics
    {
        // 1 query: order aggregates
        $orderStats = $customer->orders()->active()
            ->selectRaw('count(*) as order_count, coalesce(sum(total), 0) as lifetime_value, max(created_at) as last_order_date')
            ->first();

        $orderCount = (int) ($orderStats->order_count ?? 0);
        $lifetimeValue = (float) ($orderStats->lifetime_value ?? 0);
        $lastOrderDate = $orderStats->last_order_date ? Date::parse($orderStats?->last_order_date) : null;

        $daysSinceLastOrder = $lastOrderDate
            ? (int) $lastOrderDate->diffInDays(now())
            : null;

        $atRiskDays = (int) Setting::get('at_risk_days', '30');
        $isAtRisk = $orderCount > 0
            && $daysSinceLastOrder !== null
            && $daysSinceLastOrder > $atRiskDays;

        // 1 query: loyalty point aggregates
        $pointStats = $customer->loyaltyPoints()
            ->selectRaw('coalesce(sum(case when type = ? then points else 0 end), 0) as earned', [LoyaltyPointType::Earned->value])
            ->selectRaw('coalesce(sum(case when type = ? then points else 0 end), 0) as adjusted', [LoyaltyPointType::Adjusted->value])
            ->selectRaw('coalesce(sum(case when type = ? then points else 0 end), 0) as redeemed', [LoyaltyPointType::Redeemed->value])
            ->first();

        $earned = (int) ($pointStats->earned ?? 0);
        $adjusted = (int) ($pointStats->adjusted ?? 0);
        $redeemed = (int) ($pointStats->redeemed ?? 0);

        return new CustomerMetrics(
            lifetimeValue: $lifetimeValue,
            orderCount: $orderCount,
            averageOrderValue: $orderCount > 0 ? $lifetimeValue / $orderCount : 0,
            lastOrderDate: $lastOrderDate,
            daysSinceLastOrder: $daysSinceLastOrder,
            isAtRisk: $isAtRisk,
            totalPoints: $earned + $adjusted - $redeemed,
            lifetimePointsEarned: $earned,
        );
    }

    /**
     * @param  Builder<Customer>  $query
     * @return Builder<Customer>
     */
    public function enrichQuery(Builder $query): Builder
    {
        return $query
            ->withCount(['orders' => function (Builder $q) {
                /** @var Builder<Order> $q */
                $q->active();
            }])
            ->withSum(['orders' => function (Builder $q) {
                /** @var Builder<Order> $q */
                $q->active();
            }], 'total')
            ->addSelect([
                'last_order_date' => Order::query()->select('created_at')
                    ->whereColumn('customer_id', 'customers.id')
                    ->latest()
                    ->limit(1),
            ]);
    }
}
