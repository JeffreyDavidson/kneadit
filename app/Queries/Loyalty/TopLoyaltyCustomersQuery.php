<?php

namespace App\Queries\Loyalty;

use App\Models\Customers\Customer;
use App\Models\Engagement\LoyaltyPoint;
use Illuminate\Database\Eloquent\Collection;

class TopLoyaltyCustomersQuery
{
    /**
     * @return Collection<int, Customer>
     */
    public static function get(int $limit = 10): Collection
    {
        return Customer::query()->select('customers.*')
            ->joinSub(LoyaltyPoint::query()->balancesByCustomer(), 'balances', 'balances.customer_id', '=', 'customers.id')
            ->addSelect('balances.balance', 'balances.earned as total_earned')
            ->orderByDesc('balances.balance')
            ->limit($limit)
            ->get();
    }
}
