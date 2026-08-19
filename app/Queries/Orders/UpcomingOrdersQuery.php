<?php

namespace App\Queries\Orders;

use App\Enums\Orders\OrderStatus;
use App\Models\Customers\Customer;
use App\Models\Orders\Order;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Date;

final class UpcomingOrdersQuery
{
    public function hasWithinDays(int $days): bool
    {
        return $this->baseQuery($days)->exists();
    }

    /**
     * @return array<string, array{label: string, orders: list<array{id: int, number: string, customer: string, items: int, total: string, time: string, status: OrderStatus}>}>
     */
    public function get(int $days): array
    {
        $orders = $this->baseQuery($days)
            ->with('customer')
            ->withCount('orderItems')
            ->oldest('delivery_date')
            ->orderBy('delivery_time')
            ->get();
        $grouped = [];

        foreach ($orders as $order) {
            $deliveryDate = $order->delivery_date;
            if ($deliveryDate === null) {
                continue;
            }

            $date = $deliveryDate->toDateString();
            $grouped[$date] ??= [
                'label' => match (true) {
                    $deliveryDate->isToday() => 'Today',
                    $deliveryDate->isTomorrow() => 'Tomorrow',
                    default => $deliveryDate->format('l, M j'),
                },
                'orders' => [],
            ];
            $customer = $order->customer;
            $grouped[$date]['orders'][] = [
                'id' => $order->id,
                'number' => $order->order_number,
                'customer' => $customer instanceof Customer ? $customer->name : 'Walk-in',
                'items' => Arr::integer($order->getAttributes(), 'order_items_count', 0),
                'total' => $order->total->formatted(),
                'time' => $order->delivery_time?->format('g:i A') ?? '',
                'status' => $order->status,
            ];
        }

        return $grouped;
    }

    /** @return \Illuminate\Database\Eloquent\Builder<Order> */
    private function baseQuery(int $days): \Illuminate\Database\Eloquent\Builder
    {
        return Order::query()
            ->active()
            ->whereBetween('delivery_date', [Date::today(), Date::today()->addDays($days)]);
    }
}
