<?php

namespace App\Reports\Customers;

use App\DataTransferObjects\Customers\CustomerReportResult;
use App\Enums\Orders\OrderStatus;
use App\Enums\Orders\PaymentStatus;
use App\Models\Customers\Customer;
use App\ValueObjects\DateRange;
use App\ValueObjects\Money;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Support\Collection;

class CustomerReport
{
    public function generate(DateRange $range): CustomerReportResult
    {
        $newCustomers = Customer::query()->whereBetween('created_at', $range->toArray())->count();

        $totalCustomersWithOrders = Customer::query()->whereHas('orders', fn (Builder $q) => $q
            ->whereNotIn('status', [OrderStatus::Cancelled])
            ->where('payment_status', PaymentStatus::Paid)
            ->whereBetween('delivery_date', $range->toArray()))->count();

        $repeatCustomers = Customer::query()->whereHas('orders', fn (Builder $q) => $q
            ->whereNotIn('status', [OrderStatus::Cancelled])
            ->where('payment_status', PaymentStatus::Paid)
            ->whereBetween('delivery_date', $range->toArray()), '>=', 2)->count();

        $repeatRate = $totalCustomersWithOrders > 0 ? round(($repeatCustomers / $totalCustomersWithOrders) * 100, 1) : 0;

        $topCustomers = array_values(Customer::query()->withSum(['orders as total_spend' => fn (EloquentBuilder $q) => $q
            ->whereNotIn('status', [OrderStatus::Cancelled])
            ->where('payment_status', PaymentStatus::Paid)
            ->whereBetween('delivery_date', $range->toArray())], 'total')
            ->withCount(['orders as order_count' => fn (EloquentBuilder $q) => $q
                ->whereNotIn('status', [OrderStatus::Cancelled])
                ->where('payment_status', PaymentStatus::Paid)
                ->whereBetween('delivery_date', $range->toArray())])
            ->orderByDesc('total_spend')
            ->get()
            ->filter(fn (Customer $c) => ((float) $c->total_spend) > 0)
            ->take(10)
            ->values()
            ->map(fn (Customer $c) => [
                'name' => $c->name,
                'email' => $c->email,
                // total_spend is SUM(orders.total) and orders.total is bigint cents
                // (migration 2026_04_22_201500).
                'total_spend' => Money::fromCents((int) $c->total_spend),
                'order_count' => (int) $c->order_count,
            ])
            ->all());

        $acquisitionByMonth = Customer::query()->whereBetween('created_at', $range->toArray())
            ->get()
            ->groupBy(fn (Customer $c) => $c->created_at?->format('Y-m') ?? '')
            ->mapWithKeys(fn (Collection $customers, int|string $month): array => [
                (string) $month => $customers->count(),
            ])
            ->sortKeys()
            ->all();

        return new CustomerReportResult(
            newCustomers: $newCustomers,
            repeatRate: $repeatRate,
            repeatCustomers: $repeatCustomers,
            totalCustomersWithOrders: $totalCustomersWithOrders,
            topCustomers: $topCustomers,
            acquisitionByMonth: $acquisitionByMonth,
        );
    }
}
