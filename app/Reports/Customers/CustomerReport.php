<?php

namespace App\Reports\Customers;

use App\DataTransferObjects\Customers\CustomerReportResult;
use App\DataTransferObjects\Customers\CustomerReportTopCustomer;
use App\Models\Customers\Customer;
use App\Models\Orders\Order;
use App\Services\Scheduling\BakeryClock;
use App\ValueObjects\DateRange;
use App\ValueObjects\Money;
use Illuminate\Support\Collection;

class CustomerReport
{
    public function generate(DateRange $range): CustomerReportResult
    {
        $createdBetween = $range->inAppTimezone()->toArray();

        $newCustomers = Customer::query()->whereBetween('created_at', $createdBetween)->count();

        $totalCustomersWithOrders = Customer::query()
            ->whereIn('id', Order::query()->revenueInDateRange($range)->select('customer_id'))
            ->count();

        $repeatCustomers = Customer::query()
            ->whereIn('id', Order::query()
                ->revenueInDateRange($range)
                ->select('customer_id')
                ->groupBy('customer_id')
                ->havingRaw('COUNT(*) >= 2'))
            ->count();

        $repeatRate = $totalCustomersWithOrders > 0 ? round(($repeatCustomers / $totalCustomersWithOrders) * 100, 1) : 0;

        $topCustomers = array_values(Customer::query()
            ->whereIn('id', Order::query()
                ->revenueInDateRange($range)
                ->select('customer_id'))
            ->withPaidOrderMetrics($range)
            ->orderByDesc('total_spend')
            ->get()
            ->filter(fn (Customer $c): bool => ((float) $c->total_spend) > 0)
            ->take(10)
            ->values()
            ->map(fn (Customer $c): CustomerReportTopCustomer => new CustomerReportTopCustomer(
                name: $c->name,
                email: $c->email,
                // total_spend is SUM(orders.total) and orders.total is bigint cents
                // (migration 2026_04_22_201500).
                totalSpend: Money::fromCents((int) $c->total_spend),
                orderCount: (int) $c->order_count,
            ))
            ->all());

        // created_at is stored in the app timezone; bucket by the bakery-local month.
        $bakeryTimezone = resolve(BakeryClock::class)->now()->getTimezone();

        $acquisitionByMonth = Customer::query()->whereBetween('created_at', $createdBetween)
            ->get()
            ->groupBy(fn (Customer $c): string => $c->created_at?->copy()->setTimezone($bakeryTimezone)->format('Y-m') ?? '')
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
