<?php

namespace App\DataTransferObjects\Delivery;

final readonly class DeliveryRouteSummary
{
    public function __construct(
        public int $totalOrders,
        public float $totalRevenue,
        public int $estimatedTotalTime,
        public float $averageDistanceTime,
    ) {}

    /** @return array{total_orders: int, total_revenue: float, estimated_total_time: int, average_distance_time: float} */
    public function toArray(): array
    {
        return [
            'total_orders' => $this->totalOrders,
            'total_revenue' => $this->totalRevenue,
            'estimated_total_time' => $this->estimatedTotalTime,
            'average_distance_time' => $this->averageDistanceTime,
        ];
    }
}
