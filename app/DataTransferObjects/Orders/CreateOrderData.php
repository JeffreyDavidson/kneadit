<?php

namespace App\DataTransferObjects\Orders;

final readonly class CreateOrderData
{
    /**
     * @param array<int, array{product_id: int, quantity: int}> $items
     */
    public function __construct(
        public string $customerName,
        public string $customerEmail,
        public string $deliveryDate,
        public string $deliveryType,
        public array $items,
        public ?string $customerPhone = null,
        public ?string $customerBirthday = null,
        public ?string $deliveryTime = null,
        public ?string $deliveryAddress = null,
        public ?string $deliveryTier = null,
        public ?string $notes = null,
        public ?string $couponCode = null,
        public ?int $couponId = null,
        public ?int $giftCardId = null,
    ) {}

    /**
     * Create from validated request data.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            customerName: $data['customer_name'],
            customerEmail: $data['customer_email'],
            deliveryDate: $data['delivery_date'],
            deliveryType: $data['delivery_type'],
            items: $data['items'],
            customerPhone: $data['customer_phone'] ?? null,
            customerBirthday: $data['customer_birthday'] ?? null,
            deliveryTime: $data['delivery_time'] ?? null,
            deliveryAddress: $data['delivery_address'] ?? null,
            deliveryTier: $data['delivery_tier'] ?? null,
            notes: $data['notes'] ?? null,
            couponCode: $data['coupon_code'] ?? null,
            couponId: isset($data['coupon_id']) ? (int) $data['coupon_id'] : null,
            giftCardId: isset($data['gift_card_id']) ? (int) $data['gift_card_id'] : null,
        );
    }
}
