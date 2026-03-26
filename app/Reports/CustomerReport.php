<?php

namespace App\Reports;

use App\Enums\PaymentStatus;
use App\Models\Customer;
use App\ValueObjects\DateRange;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Support\Facades\DB;

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
            ->having('total_spend', '>', 0)
            ->orderByDesc('total_spend')
            ->limit(10)
            ->get()
            ->map(fn (Customer $c) => [
                'name' => $c->name,
                'email' => $c->email,
                'total_spend' => (float) $c->total_spend,
                'order_count' => (int) $c->order_count,
            ])
            ->all();

        $acquisitionByMonth = Customer::query()->whereBetween('created_at', $range->toArray())
            ->select(DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"), DB::raw('COUNT(*) as count'))
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('count', 'month')
            ->toArray();

        return compact('newCustomers', 'repeatRate', 'repeatCustomers', 'totalCustomersWithOrders', 'topCustomers', 'acquisitionByMonth');
    }
}
