<?php

namespace App\Services;

use App\DataTransferObjects\CustomerMetrics;
use App\Enums\OrderStatus;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Setting;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class CustomerIntelligence
{
    public function metrics(Customer $customer): CustomerMetrics
    {
        $lifetimeValue = (float) $customer->orders()->active()->sum('total');
        $orderCount = $customer->orders()->active()->count();
        $lastOrderDate = $customer->orders()->latest()->value('created_at');
        $lastOrderCarbon = $lastOrderDate ? Carbon::parse($lastOrderDate) : null;

        $daysSinceLastOrder = $lastOrderCarbon
            ? (int) $lastOrderCarbon->diffInDays(now())
            : null;

        $atRiskDays = (int) Setting::get('at_risk_days', '30');
        $isAtRisk = $orderCount > 0
            && $daysSinceLastOrder !== null
            && $daysSinceLastOrder > $atRiskDays;

        $earned = (int) $customer->loyaltyPoints()->earned()->sum('points');
        $adjusted = (int) $customer->loyaltyPoints()->adjusted()->sum('points');
        $redeemed = (int) $customer->loyaltyPoints()->redeemed()->sum('points');

        return new CustomerMetrics(
            lifetimeValue: $lifetimeValue,
            orderCount: $orderCount,
            averageOrderValue: $orderCount > 0 ? $lifetimeValue / $orderCount : 0,
            lastOrderDate: $lastOrderCarbon,
            daysSinceLastOrder: $daysSinceLastOrder,
            isAtRisk: $isAtRisk,
            totalPoints: $earned + $adjusted - $redeemed,
            lifetimePointsEarned: $earned,
        );
    }

    public static function enrichQuery(Builder $query): Builder
    {
        return $query
            ->withCount(['orders' => fn (Builder $q) => $q->where('status', '!=', OrderStatus::Cancelled)])
            ->withSum(['orders' => fn (Builder $q) => $q->where('status', '!=', OrderStatus::Cancelled)], 'total')
            ->addSelect([
                'last_order_date' => Order::select('created_at')
                    ->whereColumn('customer_id', 'customers.id')
                    ->latest()
                    ->limit(1),
            ]);
    }
}
