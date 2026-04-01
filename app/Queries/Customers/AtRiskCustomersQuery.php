<?php

namespace App\Queries\Customers;

use App\Builders\Customers\CustomerQueryBuilder;
use App\Models\Customers\Customer;
use Illuminate\Database\Eloquent\Collection;

class AtRiskCustomersQuery
{
    /**
     * Get customers who have ordered before but not in the last N days.
     *
     * @return Collection<int, Customer>
     */
    public static function get(int $days = 30, ?int $limit = null): Collection
    {
        $query = self::query($days)->orderBy('name');

        if ($limit) {
            $query->limit($limit);
        }

        return $query->get();
    }

    /**
     * Get the base query for at-risk customers.
     */
    public static function query(int $days = 30): CustomerQueryBuilder
    {
        return Customer::query()->atRisk($days);
    }

    /**
     * Get count of at-risk customers.
     */
    public static function count(int $days = 30): int
    {
        return self::query($days)->count();
    }
}
