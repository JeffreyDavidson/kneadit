<?php

namespace App\Queries\Customers;

use App\Builders\Customers\CustomerQueryBuilder;
use App\Models\Customers\Customer;
use App\Models\Orders\Order;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\Relation;

final class CustomerDirectoryQuery
{
    /** @return Collection<int, Customer> */
    public static function search(string $search = ''): Collection
    {
        return Customer::query()
            ->withCount('orders')
            ->withSum('orders', 'total')
            ->addSelect([
                'last_order_date' => Order::query()
                    ->select('created_at')
                    ->whereColumn('customer_id', 'customers.id')
                    ->latest()
                    ->limit(1),
            ])
            ->when($search !== '', function (CustomerQueryBuilder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->whereLike('name', "%{$search}%")
                        ->orWhereLike('email', "%{$search}%")
                        ->orWhereLike('phone', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->get();
    }

    public static function findWithDetails(int $customerId): ?Customer
    {
        return Customer::query()
            ->with([
                'orders' => function (Relation $query): void {
                    $query->latest();
                },
                'customerNotes' => function (Relation $query): void {
                    $query->with('createdBy')->latest();
                },
            ])
            ->find($customerId);
    }
}
