<?php

namespace App\Services\Delivery;

use App\Models\Order;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Date;

class DeliveryRouteService
{
    /**
     * Load delivery orders for a given date.
     *
     * @return Collection<int, mixed>
     */
    public function loadOrders(string $date): Collection
    {
        return Order::with(['customer', 'orderItems'])
            ->whereDate('delivery_date', $date)
            ->whereNotNull('delivery_address')
            ->where('delivery_address', '!=', '')
            ->orderBy('delivery_time')
            ->get()
            ->map(function (Order $order) {
                return [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'customer_name' => $order->customer->name ?? 'Unknown Customer',
                    'delivery_address' => $order->delivery_address,
                    'delivery_time' => $order->delivery_time ? Date::parse($order->delivery_time)->format('H:i') : 'Not specified',
                    'total' => $order->total,
                    'distance_tier' => $this->calculateDistanceTier($order->delivery_address ?? ''),
                ];
            });
    }

    /**
     * Estimate distance tier based on address keywords.
     *
     * @return array<string, mixed>
     */
    public function calculateDistanceTier(string $deliveryAddress): array
    {
        $address = strtolower($deliveryAddress);

        if (str_contains($address, 'downtown') ||
            str_contains($address, 'center') ||
            str_contains($address, 'main st')) {
            return ['tier' => 'Close', 'color' => 'green', 'estimated_minutes' => 10];
        }

        if (str_contains($address, 'west') ||
            str_contains($address, 'east') ||
            str_contains($address, 'north') ||
            str_contains($address, 'south')) {
            return ['tier' => 'Medium', 'color' => 'yellow', 'estimated_minutes' => 20];
        }

        return ['tier' => 'Far', 'color' => 'red', 'estimated_minutes' => 35];
    }

    /**
     * Calculate route statistics from loaded orders.
     *
     * @param  Collection<int, mixed>  $deliveryOrders
     * @return array<string, mixed>
     */
    public function getRouteStats(Collection $deliveryOrders): array
    {
        $totalOrders = $deliveryOrders->count();
        $totalRevenue = $deliveryOrders->sum('total');
        $averageDistance = $deliveryOrders->avg(function (array $order) {
            return $order['distance_tier']['estimated_minutes'];
        });

        return [
            'total_orders' => $totalOrders,
            'total_revenue' => $totalRevenue,
            'estimated_total_time' => $deliveryOrders->sum(function (array $order) {
                return $order['distance_tier']['estimated_minutes'];
            }),
            'average_distance_time' => round($averageDistance ?? 0, 1),
        ];
    }
}
