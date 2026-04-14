<?php

namespace App\Queries\Loyalty;

use App\Enums\Engagement\LoyaltyPointType;
use App\Models\Customers\Customer;
use Illuminate\Database\Eloquent\Collection;

class TopLoyaltyCustomersQuery
{
    /**
     * @return Collection<int, Customer>
     */
    public static function get(int $limit = 10): Collection
    {
        return Customer::query()->select('customers.*')
            ->join('loyalty_points', 'customers.id', '=', 'loyalty_points.customer_id')
            ->groupBy('customers.id')
            ->selectRaw(
                'SUM(CASE WHEN loyalty_points.type = ? THEN loyalty_points.points ELSE 0 END) '
                . '- SUM(CASE WHEN loyalty_points.type = ? THEN loyalty_points.points ELSE 0 END) as balance',
                [LoyaltyPointType::Earned->value, LoyaltyPointType::Redeemed->value],
            )
            ->selectRaw(
                'SUM(CASE WHEN loyalty_points.type = ? THEN loyalty_points.points ELSE 0 END) as total_earned',
                [LoyaltyPointType::Earned->value],
            )
            ->orderByDesc('balance')
            ->limit($limit)
            ->get();
    }
}
