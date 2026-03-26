<?php

namespace App\Builders;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\ValueObjects\DateRange;
use Illuminate\Database\Eloquent\Builder;

/** @extends Builder<Order> */
class OrderQueryBuilder extends Builder
{
    public function paid(): static
    {
        $this->where('payment_status', PaymentStatus::Paid);

        return $this;
    }

    public function active(): static
    {
        $this->whereNotIn('status', [OrderStatus::Cancelled]);

        return $this;
    }

    public function byStatus(OrderStatus $status): static
    {
        $this->where('status', $status);

        return $this;
    }

    public function paidInYear(int $year): static
    {
        $this->whereYear('delivery_date', $year)
            ->where('payment_status', PaymentStatus::Paid);

        return $this;
    }

    public function paidInDateRange(DateRange $range): static
    {
        $this->whereBetween('delivery_date', $range->toArray())
            ->where('payment_status', PaymentStatus::Paid);

        return $this;
    }

    public function inDateRange(DateRange $range): static
    {
        $this->whereBetween('delivery_date', $range->toArray());

        return $this;
    }
}
