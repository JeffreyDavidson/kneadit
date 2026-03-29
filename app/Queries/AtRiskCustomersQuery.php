<?php

namespace App\Queries;

use App\Enums\OrderStatus;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Builder;
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
        $query = Customer::query()
            ->whereHas('orders', fn (Builder $q) => $q->whereNotIn('status', [OrderStatus::Cancelled]))
            ->whereDoesntHave('orders', fn (Builder $q) => $q
                ->whereNotIn('status', [OrderStatus::Cancelled])
                ->where('created_at', '>=', now()->subDays($days)))
            ->orderBy('name');

        if ($limit) {
            $query->limit($limit);
        }

        return $query->get();
    }

    /**
     * Get count of at-risk customers.
     */
    public static function count(int $days = 30): int
    {
        return Customer::query()
            ->whereHas('orders', fn (Builder $q) => $q->whereNotIn('status', [OrderStatus::Cancelled]))
            ->whereDoesntHave('orders', fn (Builder $q) => $q
                ->whereNotIn('status', [OrderStatus::Cancelled])
                ->where('created_at', '>=', now()->subDays($days)))
            ->count();
    }
}
