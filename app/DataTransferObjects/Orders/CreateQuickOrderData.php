<?php

namespace App\DataTransferObjects\Orders;

final readonly class CreateQuickOrderData
{
    /**
     * @param array<int, array{product_id: int, quantity: int, unit_price: float, special_instructions?: string|null}> $orderItems
     */
    public function __construct(
        public string $customerName,
        public string $paymentMethod,
        public string $deliveryType,
        public string $deliveryDate,
        public string $deliveryTime,
        public array $orderItems,
        public ?string $customerEmail = null,
        public ?string $customerPhone = null,
        public ?string $deliveryAddress = null,
        public ?string $notes = null,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            customerName: $data['customer_name'],
            paymentMethod: $data['payment_method'],
            deliveryType: $data['delivery_type'],
            deliveryDate: $data['delivery_date'],
            deliveryTime: $data['delivery_time'],
            orderItems: $data['order_items'] ?? [],
            customerEmail: $data['customer_email'] ?? null,
            customerPhone: $data['customer_phone'] ?? null,
            deliveryAddress: $data['delivery_address'] ?? null,
            notes: $data['notes'] ?? null,
        );
    }
}
