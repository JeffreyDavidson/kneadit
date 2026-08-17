<?php

namespace App\Services\Delivery;

use App\Enums\Orders\DeliveryDistanceTier;
use App\Models\Orders\Order;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Str;

/**
 * @phpstan-type DistanceTier array{tier: string, color: string, estimated_minutes: int}
 * @phpstan-type DeliveryOrder array{id: int, order_number: string, customer_name: string, delivery_address: ?string, delivery_time: non-falsy-string, total: float, distance_tier: DistanceTier}
 */
class DeliveryRouteService
{
    /**
     * Load delivery orders for a given date.
     *
     * @return Collection<int, DeliveryOrder>
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
                    'total' => $order->total->dollars(),
                    'distance_tier' => $this->calculateDistanceTier($order->delivery_address ?? ''),
                ];
            });
    }

    /**
     * Estimate distance tier based on address keywords.
     *
     * @return DistanceTier
     */
    public function calculateDistanceTier(string $deliveryAddress): array
    {
        $address = Str::lower($deliveryAddress);

        $tier = match (true) {
            Str::contains($address, ['downtown', 'center', 'main st']) => DeliveryDistanceTier::Close,
            Str::contains($address, ['west', 'east', 'north', 'south']) => DeliveryDistanceTier::Medium,
            default => DeliveryDistanceTier::Far,
        };

        return [
            'tier' => $tier->getLabel(),
            'color' => $tier->getColor(),
            'estimated_minutes' => $tier->estimatedMinutes(),
        ];
    }

    /**
     * Calculate route statistics from loaded orders.
     *
     * @param Collection<int, DeliveryOrder> $deliveryOrders
     * @return array{total_orders: int, total_revenue: float, estimated_total_time: int, average_distance_time: float}
     */
    public function getRouteStats(Collection $deliveryOrders): array
    {
        $totalOrders = $deliveryOrders->count();
        $totalRevenue = $deliveryOrders->sum(fn (array $order): float => $order['total']);
        $averageDistance = $deliveryOrders->avg(function (array $order): int {
            return $order['distance_tier']['estimated_minutes'];
        });

        return [
            'total_orders' => $totalOrders,
            'total_revenue' => $totalRevenue,
            'estimated_total_time' => $deliveryOrders->sum(function (array $order): int {
                return $order['distance_tier']['estimated_minutes'];
            }),
            'average_distance_time' => round($averageDistance ?? 0, 1),
        ];
    }
}
