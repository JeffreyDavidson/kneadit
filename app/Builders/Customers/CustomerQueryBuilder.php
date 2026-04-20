<?php

namespace App\Builders\Customers;

use App\Builders\Orders\OrderQueryBuilder;
use App\Enums\Orders\OrderStatus;
use App\Models\Customers\Customer;
use App\Models\Orders\Order;
use Illuminate\Database\Eloquent\Builder;

/** @extends Builder<Customer> */
class CustomerQueryBuilder extends Builder
{
    public function atRisk(int $days = 30): static
    {
        $this->whereHas('orders', fn (Builder $q) => $q->whereNotIn('status', [OrderStatus::Cancelled]))
            ->whereDoesntHave('orders', fn (Builder $q) => $q
                ->whereNotIn('status', [OrderStatus::Cancelled])
                ->where('created_at', '>=', now()->subDays($days)));

        return $this;
    }

    /**
     * Eager-load aggregate order metrics (count, total sum, last order date)
     * onto each customer. Cancelled orders are excluded from count and sum.
     */
    public function withOrderMetrics(): static
    {
        $this->withCount(['orders' => function (OrderQueryBuilder $q) {
            $q->active();
        }])
            ->withSum(['orders' => function (OrderQueryBuilder $q) {
                $q->active();
            }], 'total')
            ->addSelect([
                'last_order_date' => Order::query()->select('created_at')
                    ->whereColumn('customer_id', 'customers.id')
                    ->latest()
                    ->limit(1),
            ]);

        return $this;
    }

    public function newThisWeek(): static
    {
        $this->where('created_at', '>=', now()->startOfWeek());

        return $this;
    }
}
