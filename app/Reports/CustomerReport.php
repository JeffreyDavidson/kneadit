<?php

namespace App\Reports;

use App\Enums\PaymentStatus;
use App\Models\Customer;
use App\ValueObjects\DateRange;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

class CustomerReport
{
    /** @return array<string, mixed> */
    public function generate(DateRange $range): array
    {
        $newCustomers = Customer::query()->whereBetween('created_at', $range->toArray())->count();

        $totalCustomersWithOrders = Customer::query()->whereHas('orders', fn (Builder $q) => $q->whereBetween('delivery_date', $range->toArray()))->count();

        $repeatCustomers = Customer::query()->whereHas('orders', fn (Builder $q) => $q->whereBetween('delivery_date', $range->toArray()), '>=', 2)->count();

        $repeatRate = $totalCustomersWithOrders > 0 ? round(($repeatCustomers / $totalCustomersWithOrders) * 100, 1) : 0;

        $topCustomers = Customer::query()->withSum(['orders as total_spend' => fn (EloquentBuilder $q) => $q->whereBetween('delivery_date', $range->toArray())->where('payment_status', PaymentStatus::Paid)], 'total')
            ->withCount(['orders as order_count' => fn (EloquentBuilder $q) => $q->whereBetween('delivery_date', $range->toArray())])
            ->orderByDesc('total_spend')
            ->get()
            ->filter(fn (Customer $c) => ((float) $c->total_spend) > 0)
            ->take(10)
            ->values()
            ->map(fn (Customer $c) => [
                'name' => $c->name,
                'email' => $c->email,
                'total_spend' => (float) $c->total_spend,
                'order_count' => (int) $c->order_count,
            ])
            ->all();

        $acquisitionByMonth = Customer::query()->whereBetween('created_at', $range->toArray())
            ->get()
            ->groupBy(fn (Customer $c) => $c->created_at->format('Y-m'))
            ->map(fn ($customers) => $customers->count())
            ->sortKeys()
            ->all();

        return compact('newCustomers', 'repeatRate', 'repeatCustomers', 'totalCustomersWithOrders', 'topCustomers', 'acquisitionByMonth');
    }
}
