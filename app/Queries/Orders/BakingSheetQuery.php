<?php

namespace App\Queries\Orders;

use App\Enums\Orders\OrderStatus;
use App\Models\Orders\OrderItem;
use Illuminate\Contracts\Database\Query\Expression;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BakingSheetQuery
{
    /** @return Collection<int, OrderItem> */
    public static function forDate(string $date): Collection
    {
        return OrderItem::query()
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('customers', 'orders.customer_id', '=', 'customers.id')
            ->whereDate('orders.delivery_date', $date)
            ->whereIn('orders.status', [OrderStatus::Confirmed->value, OrderStatus::Baking->value])
            ->select([
                'products.name as product_name',
                DB::raw('SUM(order_items.quantity) as total_quantity'),
                self::customerNamesExpression(),
            ])
            ->groupBy('products.id', 'products.name')
            ->orderBy('products.name')
            ->get();
    }

    private static function customerNamesExpression(): Expression
    {
        return match (DB::getDriverName()) {
            'sqlite' => DB::raw("group_concat(customers.name, ', ') as customer_names"),
            'pgsql' => DB::raw("STRING_AGG(customers.name, ', ') as customer_names"),
            default => DB::raw("GROUP_CONCAT(customers.name SEPARATOR ', ') as customer_names"),
        };
    }
}
