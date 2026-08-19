<?php

namespace App\Queries\Orders;

use App\Enums\Orders\OrderStatus;
use App\Models\Orders\OrderItem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;

class BakingSheetQuery
{
    public static function hasDashboardItems(): bool
    {
        return self::dashboardItems()->exists();
    }

    /** @return list<array{product_id: int, name: string, quantity: int}> */
    public static function forDashboard(): array
    {
        $rows = self::dashboardItems()
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->selectRaw('order_items.product_id, products.name as product_name, SUM(order_items.quantity) as total_quantity')
            ->groupBy('order_items.product_id', 'products.name')
            ->orderByDesc('total_quantity')
            ->get()
            ->map(function (OrderItem $item): array {
                $attributes = $item->getAttributes();

                return [
                    'product_id' => Arr::integer($attributes, 'product_id'),
                    'name' => Arr::string($attributes, 'product_name', 'Unknown Product'),
                    'quantity' => Arr::integer($attributes, 'total_quantity'),
                ];
            })
            ->all();

        return array_values($rows);
    }

    /** @return Collection<int, OrderItem> */
    public static function forDate(string $date): Collection
    {
        $groupConcat = DB::getDriverName() === 'sqlite'
            ? DB::raw("group_concat(customers.name, ', ') as customer_names")
            : DB::raw("GROUP_CONCAT(customers.name SEPARATOR ', ') as customer_names");

        return OrderItem::query()
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('customers', 'orders.customer_id', '=', 'customers.id')
            ->whereDate('orders.delivery_date', $date)
            ->whereIn('orders.status', [OrderStatus::Confirmed->value, OrderStatus::Baking->value])
            ->select([
                'products.name as product_name',
                DB::raw('SUM(order_items.quantity) as total_quantity'),
                $groupConcat,
            ])
            ->groupBy('products.id', 'products.name')
            ->orderBy('products.name')
            ->get();
    }

    /** @return Builder<OrderItem> */
    private static function dashboardItems(): Builder
    {
        return OrderItem::query()
            ->whereHas('order', function (Builder $query): void {
                $query->whereIn('status', [OrderStatus::Pending, OrderStatus::Confirmed, OrderStatus::Baking])
                    ->where(function (Builder $dateQuery): void {
                        $dateQuery->whereDate('delivery_date', Date::today())
                            ->orWhere(function (Builder $futureQuery): void {
                                $futureQuery->whereDate('delivery_date', '>', Date::today())
                                    ->where('status', OrderStatus::Confirmed);
                            });
                    });
            });
    }
}
