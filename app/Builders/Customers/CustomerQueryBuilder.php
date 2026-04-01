<?php

namespace App\Builders\Customers;

use App\Enums\Orders\OrderStatus;
use App\Models\Customers\Customer;
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

    public function withOrderMetrics(): static
    {
        $this->withCount('orders')->withSum('orders', 'total');

        return $this;
    }

    public function newThisWeek(): static
    {
        $this->where('created_at', '>=', now()->startOfWeek());

        return $this;
    }
}
