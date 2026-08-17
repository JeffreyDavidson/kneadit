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
            customerName: self::stringValue($data['customer_name'] ?? null, 'customer_name'),
            paymentMethod: self::stringValue($data['payment_method'] ?? null, 'payment_method'),
            deliveryType: self::stringValue($data['delivery_type'] ?? null, 'delivery_type'),
            deliveryDate: self::stringValue($data['delivery_date'] ?? null, 'delivery_date'),
            deliveryTime: self::stringValue($data['delivery_time'] ?? null, 'delivery_time'),
            orderItems: self::orderItems($data['order_items'] ?? []),
            customerEmail: self::nullableStringValue($data['customer_email'] ?? null, 'customer_email'),
            customerPhone: self::nullableStringValue($data['customer_phone'] ?? null, 'customer_phone'),
            deliveryAddress: self::nullableStringValue($data['delivery_address'] ?? null, 'delivery_address'),
            notes: self::nullableStringValue($data['notes'] ?? null, 'notes'),
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

    /** @return array<int, array{product_id: int, quantity: int, unit_price: float, special_instructions?: string|null}> */
    private static function orderItems(mixed $value): array
    {
        if (! is_array($value)) {
            throw new \UnexpectedValueException('Expected order_items to be an array.');
        }

        $orderItems = [];

        foreach (array_values($value) as $index => $item) {
            if (! is_array($item)) {
                throw new \UnexpectedValueException("Expected order_items.{$index} to be an array.");
            }

            $orderItem = [
                'product_id' => self::integerValue($item['product_id'] ?? null, "order_items.{$index}.product_id"),
                'quantity' => self::integerValue($item['quantity'] ?? null, "order_items.{$index}.quantity"),
                'unit_price' => self::floatValue($item['unit_price'] ?? null, "order_items.{$index}.unit_price"),
            ];

            if (array_key_exists('special_instructions', $item)) {
                $orderItem['special_instructions'] = self::nullableStringValue($item['special_instructions'], "order_items.{$index}.special_instructions");
            }

            $orderItems[] = $orderItem;
        }

        return $orderItems;
    }
}
