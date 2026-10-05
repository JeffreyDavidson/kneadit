<?php

namespace App\Builders\Customers;

use App\Builders\Orders\OrderQueryBuilder;
use App\Enums\Orders\OrderStatus;
use App\Models\Customers\Customer;
use App\Models\Orders\Order;
use App\Support\EmailAddress;
use App\ValueObjects\DateRange;
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
     * onto each customer. Cancelled orders are excluded from the count and last
     * order date; the sum (lifetime value) and revenue_orders_count only cover
     * revenue orders (paid, not cancelled).
     */
    public function withOrderMetrics(): static
    {
        $this->withCount(['orders' => function (OrderQueryBuilder $q): void {
            $q->active();
        }])
            ->withCount(['orders as revenue_orders_count' => function (OrderQueryBuilder $q): void {
                $q->revenue();
            }])
            ->withSum(['orders' => function (OrderQueryBuilder $q): void {
                $q->revenue();
            }], 'total')
            ->addSelect([
                'last_order_date' => Order::query()->select('created_at')
                    ->whereColumn('customer_id', 'customers.id')
                    ->active()
                    ->latest()
                    ->limit(1),
            ]);

        return $this;
    }

    public function withPaidOrderMetrics(DateRange $range): static
    {
        $paidOrders = function (OrderQueryBuilder $query) use ($range): void {
            $query->revenueInDateRange($range);
        };

        $this->withSum(['orders as total_spend' => $paidOrders], 'total')
            ->withCount(['orders as order_count' => $paidOrders]);

        return $this;
    }

    public function withRfmMetrics(): static
    {
        $paidOrders = function (OrderQueryBuilder $query): void {
            $query->revenue();
        };

        $this->whereIn('id', Order::query()->revenue()->select('customer_id'))
            ->withCount(['orders as frequency' => $paidOrders])
            ->withSum(['orders as monetary_cents' => $paidOrders], 'total')
            ->withMax(['orders as last_order_at' => $paidOrders], 'delivery_date');

        return $this;
    }

    /**
     * Customers who can still receive marketing email (have not unsubscribed).
     */
    public function subscribedToMarketing(): static
    {
        $this->whereNull('marketing_opted_out_at');

        return $this;
    }

    /** Customers who have verified their email address. */
    public function emailVerified(): static
    {
        $this->whereNotNull('email_verified_at');

        return $this;
    }

    public function unsubscribedFromMarketing(): static
    {
        $this->whereNotNull('marketing_opted_out_at');

        return $this;
    }

    /**
     * Customers with at least one open order (pending, confirmed, baking or ready).
     */
    public function withOpenOrder(): static
    {
        $this->whereExists(
            Order::query()
                ->select('id')
                ->whereColumn('orders.customer_id', 'customers.id')
                ->outstanding(),
        );

        return $this;
    }

    public function newThisWeek(): static
    {
        $this->where('created_at', '>=', now()->startOfWeek());

        return $this;
    }

    public function forEmail(string $email): static
    {
        $this->where('email', EmailAddress::normalize($email));

        return $this;
    }

    public function forReferralCode(string $code): static
    {
        $this->where('referral_code', $code);

        return $this;
    }
}
