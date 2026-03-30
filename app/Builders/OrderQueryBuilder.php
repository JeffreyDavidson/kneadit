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

    /**
     * Filter orders for delivery on a specific date with active delivery statuses.
     */
    public function forDeliveryOnDate(\Illuminate\Support\Carbon $date): static
    {
        $this->with(['customer', 'orderItems.product'])
            ->whereNotNull('delivery_address')
            ->where('delivery_address', '!=', '')
            ->whereIn('status', [OrderStatus::Confirmed, OrderStatus::Baking, OrderStatus::Ready])
            ->whereDate('delivery_date', $date)
            ->orderBy('delivery_time');

        return $this;
    }

    /**
     * Filter orders by customer email via relationship.
     */
    public function forCustomerEmail(string $email): static
    {
        $this->whereHas('customer', fn (Builder $q) => $q->where('email', $email))
            ->with(['customer', 'orderItems.product', 'messages'])
            ->latest()
            ->limit(50);

        return $this;
    }
}
