<?php

namespace App\Services\Delivery;

use App\DataTransferObjects\Delivery\DeliveryEstimate;
use App\DataTransferObjects\Delivery\DeliveryRouteSummary;
use App\DataTransferObjects\Delivery\DeliveryStop;
use App\Enums\Orders\DeliveryDistanceTier;
use App\Models\Orders\Order;
use App\Queries\Delivery\DeliveryOrdersQuery;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Str;

class DeliveryRouteService
{
    /**
     * Load delivery orders for a given date.
     *
     * @return Collection<int, DeliveryStop>
     */
    public function loadOrders(string $date): Collection
    {
        return DeliveryOrdersQuery::forDate($date)->map(fn (Order $order): DeliveryStop => new DeliveryStop(
            orderId: $order->id,
            orderNumber: $order->order_number,
            customerName: $order->customer->name ?? 'Unknown Customer',
            deliveryAddress: $order->delivery_address,
            deliveryTime: $order->delivery_time ? Date::parse($order->delivery_time)->format('H:i') : 'Not specified',
            total: $order->total->dollars(),
            estimate: $this->calculateDistanceTier($order->delivery_address ?? ''),
        ));
    }

    /**
     * Estimate distance tier based on address keywords.
     */
    public function calculateDistanceTier(string $deliveryAddress): DeliveryEstimate
    {
        $address = Str::lower($deliveryAddress);

        $tier = match (true) {
            Str::contains($address, ['downtown', 'center', 'main st']) => DeliveryDistanceTier::Close,
            Str::contains($address, ['west', 'east', 'north', 'south']) => DeliveryDistanceTier::Medium,
            default => DeliveryDistanceTier::Far,
        };

        return new DeliveryEstimate($tier);
    }

    /**
     * Calculate route statistics from loaded orders.
     *
     * @param Collection<int, DeliveryStop> $deliveryOrders
     */
    public function getRouteStats(Collection $deliveryOrders): DeliveryRouteSummary
    {
        $totalOrders = $deliveryOrders->count();
        $totalRevenue = $deliveryOrders->sum(fn (DeliveryStop $stop): float => $stop->total);
        $averageDistance = $deliveryOrders->avg(fn (DeliveryStop $stop): int => $stop->estimate->estimatedMinutes());

        return new DeliveryRouteSummary(
            totalOrders: $totalOrders,
            totalRevenue: $totalRevenue,
            estimatedTotalTime: $deliveryOrders->sum(fn (DeliveryStop $stop): int => $stop->estimate->estimatedMinutes()),
            averageDistanceTime: round($averageDistance ?? 0, 1),
        );
    }
}
