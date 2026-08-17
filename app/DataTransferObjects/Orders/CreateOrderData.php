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
        public float $tipAmount = 0.0,
        public ?string $pickupContactName = null,
        public ?string $pickupContactPhone = null,
        public ?string $pickupContactEmail = null,
    ) {}

    /**
     * Create from validated request data.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            customerName: self::stringValue($data['customer_name'] ?? null, 'customer_name'),
            customerEmail: self::stringValue($data['customer_email'] ?? null, 'customer_email'),
            deliveryDate: self::stringValue($data['delivery_date'] ?? null, 'delivery_date'),
            deliveryType: self::stringValue($data['delivery_type'] ?? null, 'delivery_type'),
            items: self::items($data['items'] ?? null),
            customerPhone: self::nullableStringValue($data['customer_phone'] ?? null, 'customer_phone'),
            customerBirthday: self::nullableStringValue($data['customer_birthday'] ?? null, 'customer_birthday'),
            deliveryTime: self::nullableStringValue($data['delivery_time'] ?? null, 'delivery_time'),
            deliveryAddress: self::nullableStringValue($data['delivery_address'] ?? null, 'delivery_address'),
            deliveryTier: self::nullableStringValue($data['delivery_tier'] ?? null, 'delivery_tier'),
            notes: self::nullableStringValue($data['notes'] ?? null, 'notes'),
            couponCode: self::nullableStringValue($data['coupon_code'] ?? null, 'coupon_code'),
            couponId: isset($data['coupon_id']) ? self::integerValue($data['coupon_id'], 'coupon_id') : null,
            giftCardId: isset($data['gift_card_id']) ? self::integerValue($data['gift_card_id'], 'gift_card_id') : null,
            tipAmount: isset($data['tip_amount']) ? self::floatValue($data['tip_amount'], 'tip_amount') : 0.0,
            pickupContactName: self::nullableStringValue($data['pickup_contact_name'] ?? null, 'pickup_contact_name'),
            pickupContactPhone: self::nullableStringValue($data['pickup_contact_phone'] ?? null, 'pickup_contact_phone'),
            pickupContactEmail: self::nullableStringValue($data['pickup_contact_email'] ?? null, 'pickup_contact_email'),
        );
    }

    private static function stringValue(mixed $value, string $key): string
    {
        if (! is_string($value)) {
            throw new \UnexpectedValueException("Expected {$key} to be a string.");
        }

        return $value;
    }

    private static function nullableStringValue(mixed $value, string $key): ?string
    {
        if ($value === null) {
            return null;
        }

        return self::stringValue($value, $key);
    }

    private static function integerValue(mixed $value, string $key): int
    {
        if (is_int($value)) {
            return $value;
        }

        if (! is_string($value) || filter_var($value, FILTER_VALIDATE_INT) === false) {
            throw new \UnexpectedValueException("Expected {$key} to be an integer.");
        }

        return (int) $value;
    }

    private static function floatValue(mixed $value, string $key): float
    {
        if (is_float($value) || is_int($value)) {
            return $value;
        }

        if (! is_string($value) || ! is_numeric($value)) {
            throw new \UnexpectedValueException("Expected {$key} to be numeric.");
        }

        return (float) $value;
    }

    /** @return array<int, array{product_id: int, quantity: int}> */
    private static function items(mixed $value): array
    {
        if (! is_array($value)) {
            throw new \UnexpectedValueException('Expected items to be an array.');
        }

        $items = [];

        foreach (array_values($value) as $index => $item) {
            if (! is_array($item)) {
                throw new \UnexpectedValueException("Expected items.{$index} to be an array.");
            }

            $items[] = [
                'product_id' => self::integerValue($item['product_id'] ?? null, "items.{$index}.product_id"),
                'quantity' => self::integerValue($item['quantity'] ?? null, "items.{$index}.quantity"),
            ];
        }

        return $items;
    }
}
