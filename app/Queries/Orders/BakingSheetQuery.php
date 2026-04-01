<?php

namespace App\Queries\Orders;

use App\Enums\Orders\OrderStatus;
use App\Models\Orders\OrderItem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BakingSheetQuery
{
    /** @return Collection<int, mixed> */
    public static function forDate(string $date): Collection
    {
        $groupConcat = DB::getDriverName() === 'sqlite'
            ? "group_concat(customers.name, ', ')"
            : "GROUP_CONCAT(customers.name SEPARATOR ', ')";

        return OrderItem::query()
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('customers', 'orders.customer_id', '=', 'customers.id')
            ->whereDate('orders.delivery_date', $date)
            ->whereIn('orders.status', [OrderStatus::Confirmed->value, OrderStatus::Baking->value])
            ->select([
                'products.name as product_name',
                DB::raw('SUM(order_items.quantity) as total_quantity'),
                DB::raw("{$groupConcat} as customer_names"),
            ])
            ->groupBy('products.id', 'products.name')
            ->orderBy('products.name')
            ->get();
    }
}
