<?php

declare(strict_types=1);

namespace App\Builders\Orders;

use App\Enums\Orders\OrderStatus;
use App\Enums\Orders\PaymentStatus;
use App\Models\Orders\Order;
use App\Support\EmailAddress;
use App\ValueObjects\DateRange;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

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

    /**
     * Unpaid orders with a PayPal invoice the hourly check should look at: every live order,
     * plus orders cancelled since $cancelledSince, which are watched only for an invoice
     * that gets paid anyway (so staff can refund it).
     */
    public function awaitingPayPalCheck(Carbon $cancelledSince): static
    {
        $this->unpaid()
            ->whereNotNull('paypal_invoice_id')
            ->where(function (Builder $query) use ($cancelledSince): void {
                $query->where('status', '!=', OrderStatus::Cancelled)
                    ->orWhere('updated_at', '>=', $cancelledSince);
            });

        return $this;
    }

    public function byStatus(OrderStatus $status): static
    {
        $this->where('status', $status);

        return $this;
    }

    /**
     * The single definition of revenue: paid (not part-paid, refunded or
     * unpaid) and not cancelled. Every revenue figure starts from this, and is
     * dated by delivery_date, the bakery-local date the order is fulfilled.
     */
    public function revenue(): static
    {
        $this->active()->paid();

        return $this;
    }

    public function revenueInYear(int $year): static
    {
        $this->revenue()->whereYear('delivery_date', $year);

        return $this;
    }

    public function revenueInDateRange(DateRange $range): static
    {
        $this->revenue()->inDateRange($range);

        return $this;
    }

    public function inDateRange(DateRange $range): static
    {
        $this->whereBetween('delivery_date', $range->toArray());

        return $this;
    }

    /**
     * Filter orders for delivery on a specific date that are ready to go out the door.
     */
    public function forDeliveryOnDate(Carbon $date): static
    {
        $this->with(['customer', 'orderItems.product'])
            ->whereNotNull('delivery_address')
            ->where('delivery_address', '!=', '')
            ->ready()
            ->whereDate('delivery_date', $date)
            ->orderBy('delivery_time');

        return $this;
    }

    /** Orders placed by the customer with this email. */
    public function placedByEmail(string $email): static
    {
        $this->whereHas('customer', fn (Builder $q) => $q->where('email', EmailAddress::normalize($email)));

        return $this;
    }

    /**
     * Filter orders by customer email via relationship.
     */
    public function forCustomerEmail(string $email): static
    {
        $this->whereHas('customer', fn (Builder $q) => $q->where('email', EmailAddress::normalize($email)))
            ->with(['customer', 'orderItems.product', 'messages'])
            ->latest()
            ->limit(50);

        return $this;
    }
}
