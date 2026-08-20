<?php

namespace App\DataTransferObjects\Delivery;

final readonly class DeliveryStop
{
    public function __construct(
        public int $orderId,
        public string $orderNumber,
        public string $customerName,
        public ?string $deliveryAddress,
        public string $deliveryTime,
        public float $total,
        public DeliveryEstimate $estimate,
    ) {}

    /** @return array{id: int, order_number: string, customer_name: string, delivery_address: ?string, delivery_time: string, total: float, distance_tier: array{tier: string, color: string, estimated_minutes: int}} */
    public function toArray(): array
    {
        return [
            'id' => $this->orderId,
            'order_number' => $this->orderNumber,
            'customer_name' => $this->customerName,
            'delivery_address' => $this->deliveryAddress,
            'delivery_time' => $this->deliveryTime,
            'total' => $this->total,
            'distance_tier' => $this->estimate->toArray(),
        ];
    }
}
