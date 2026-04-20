<?php

namespace App\Builders\Orders;

use App\Enums\Orders\OrderStatus;
use App\Enums\Orders\PaymentStatus;
use App\Models\Orders\Order;
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

    public function unpaid(): static
    {
        $this->where('payment_status', PaymentStatus::Unpaid);

        return $this;
    }

    public function pending(): static
    {
        $this->where('status', OrderStatus::Pending);

        return $this;
    }

    public function ready(): static
    {
        $this->where('status', OrderStatus::Ready);

        return $this;
    }

    public function confirmed(): static
    {
        $this->where('status', OrderStatus::Confirmed);

        return $this;
    }

    public function delivered(): static
    {
        $this->where('status', OrderStatus::Delivered);

        return $this;
    }

    public function active(): static
    {
        $this->whereNotIn('status', [OrderStatus::Cancelled]);

        return $this;
    }

    /** Pending, Confirmed, or Baking — orders still being prepared. */
    public function bakingPipeline(): static
    {
        $this->whereIn('status', [
            OrderStatus::Pending,
            OrderStatus::Confirmed,
            OrderStatus::Baking,
        ]);

        return $this;
    }

    /** Confirmed or Baking — orders that have ingredients allocated. */
    public function confirmedOrBaking(): static
    {
        $this->whereIn('status', [OrderStatus::Confirmed, OrderStatus::Baking]);

        return $this;
    }

    /** Not yet delivered or cancelled — outstanding work. */
    public function outstanding(): static
    {
        $this->whereNotIn('status', [OrderStatus::Cancelled, OrderStatus::Delivered]);

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

    public function paidInMonth(int $year, int $month): static
    {
        $this->whereYear('delivery_date', $year)
            ->whereMonth('delivery_date', $month)
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
