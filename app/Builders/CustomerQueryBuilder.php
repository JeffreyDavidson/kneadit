<?php

namespace App\Builders;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Builder;

/** @extends Builder<Customer> */
class CustomerQueryBuilder extends Builder
{
    public function atRisk(int $days = 30): static
    {
        $this->whereHas('orders')
            ->whereDoesntHave('orders', fn (Builder $q) => $q->where('created_at', '>=', now()->subDays($days)));

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
