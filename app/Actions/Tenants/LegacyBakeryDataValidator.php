<?php

namespace App\Actions\Tenants;

use App\Enums\Financial\CouponType;
use App\Enums\Orders\DeliveryType;
use App\Enums\Orders\OrderStatus;
use App\Enums\Orders\PaymentMethod;
use App\Enums\Orders\PaymentStatus;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use InvalidArgumentException;

final class LegacyBakeryDataValidator
{
    /**
     * Validate references and scalar values before the import transaction.
     *
     * @param array<string, array<int, array<string, mixed>>> $data
     */
    public function validate(array $data): void
    {
        $categoryIds = $this->legacyIds($data['categories'] ?? [], 'category');
        $productIds = $this->legacyIds($data['products'] ?? [], 'product');
        $couponIds = $this->legacyIds($data['coupons'] ?? [], 'coupon');
        $recipeIds = $this->legacyIds($data['recipes'] ?? [], 'recipe');
        $orderIds = $this->legacyIds($data['orders'] ?? [], 'order');

        foreach ($data['coupons'] ?? [] as $index => $coupon) {
            foreach (['type', 'code', 'value'] as $field) {
                if (! array_key_exists($field, $coupon)) {
                    throw new InvalidArgumentException("Coupon at index {$index} is missing a {$field}.");
                }
            }

            $this->couponType($coupon['type']);
            $this->stringValue($coupon['code']);
            $this->floatValue($coupon['value']);
        }

        foreach ($data['products'] ?? [] as $index => $product) {
            if (! array_key_exists('category_id', $product)) {
                throw new InvalidArgumentException("Product at index {$index} is missing a category ID.");
            }

            $categoryId = $this->parseLegacyInteger($product['category_id']);
            $this->assertReference($categoryIds, $categoryId, "Product at index {$index} references missing category ID {$categoryId}.");
        }

        foreach ($data['orders'] ?? [] as $index => $order) {
            foreach (['customer_email', 'customer_name', 'order_number'] as $field) {
                if (! array_key_exists($field, $order)) {
                    throw new InvalidArgumentException("Order at index {$index} is missing {$this->orderFieldLabel($field)}.");
                }
            }

            $this->orderStatus($order['status'] ?? OrderStatus::Pending->value);
            $this->paymentStatus($order['payment_status'] ?? PaymentStatus::Unpaid->value);
            $this->paymentMethod($order['payment_method'] ?? PaymentMethod::Other->value);
            $this->deliveryType($order['fulfillment_type'] ?? DeliveryType::Pickup->value);
            $this->stringValue($order['customer_name']);
            $this->stringValue($order['order_number']);

            if (array_key_exists('coupon_id', $order) && $order['coupon_id'] !== null) {
                $couponId = $this->parseLegacyInteger($order['coupon_id']);
                $this->assertReference($couponIds, $couponId, "Order at index {$index} references missing coupon ID {$couponId}.");
            }
        }

        foreach ($data['order_items'] ?? [] as $index => $item) {
            foreach (['order_id', 'product_name', 'quantity', 'unit_price'] as $field) {
                if (! array_key_exists($field, $item)) {
                    throw new InvalidArgumentException("Order item at index {$index} is missing a {$this->orderItemFieldLabel($field)}.");
                }
            }

            $orderId = $this->parseLegacyInteger($item['order_id']);
            $this->assertReference($orderIds, $orderId, "Order item at index {$index} references missing order ID {$orderId}.");
            $this->stringValue($item['product_name']);
            $this->parseLegacyInteger($item['quantity']);
            $this->floatValue($item['unit_price']);

            if (array_key_exists('product_id', $item) && $item['product_id'] !== null) {
                $productId = $this->parseLegacyInteger($item['product_id']);
                $this->assertReference($productIds, $productId, "Order item at index {$index} references missing product ID {$productId}.");
            }
        }

        foreach ($data['order_notes'] ?? [] as $index => $note) {
            if (! array_key_exists('order_id', $note)) {
                throw new InvalidArgumentException("Order note at index {$index} is missing an order ID.");
            }

            $orderId = $this->parseLegacyInteger($note['order_id']);
            $this->assertReference($orderIds, $orderId, "Order note at index {$index} references missing order ID {$orderId}.");
        }

        foreach ($data['customer_favorites'] ?? [] as $index => $favorite) {
            if (! array_key_exists('product_id', $favorite)) {
                throw new InvalidArgumentException("Customer favorite at index {$index} is missing a product ID.");
            }

            $productId = $this->parseLegacyInteger($favorite['product_id']);
            $this->assertReference($productIds, $productId, "Customer favorite at index {$index} references missing product ID {$productId}.");
        }

        $this->validateOptionalProductReferences($data['reviews'] ?? [], $productIds, 'Review');
        $this->validateOptionalProductReferences($data['recipes'] ?? [], $productIds, 'Recipe');
        $this->validateOptionalProductReferences($data['waitlist_entries'] ?? [], $productIds, 'Waitlist entry');

        foreach (Arr::reject($data['reviews'] ?? [], static fn (array $review): bool => ! array_key_exists('order_id', $review) || $review['order_id'] === null) as $index => $review) {
            $orderId = $this->parseLegacyInteger($review['order_id']);
            $this->assertReference($orderIds, $orderId, "Review at index {$index} references missing order ID {$orderId}.");
        }

        $this->validateRecipeReferences($data['recipe_ingredients'] ?? [], $recipeIds, 'Recipe ingredient');
        $this->validateRecipeReferences($data['recipe_stages'] ?? [], $recipeIds, 'Recipe stage');
    }

    /**
     * @param array<int, array<string, mixed>> $records
     * @param array<int, true> $productIds
     */
    private function validateOptionalProductReferences(array $records, array $productIds, string $dataset): void
    {
        foreach (Arr::reject($records, static fn (array $record): bool => ! array_key_exists('product_id', $record) || $record['product_id'] === null) as $index => $record) {
            $productId = $this->parseLegacyInteger($record['product_id']);
            $this->assertReference($productIds, $productId, "{$dataset} at index {$index} references missing product ID {$productId}.");
        }
    }

    /**
     * @param array<int, array<string, mixed>> $records
     * @param array<int, true> $recipeIds
     */
    private function validateRecipeReferences(array $records, array $recipeIds, string $dataset): void
    {
        foreach ($records as $index => $record) {
            if (! array_key_exists('recipe_id', $record)) {
                throw new InvalidArgumentException("{$dataset} at index {$index} is missing a recipe ID.");
            }

            $recipeId = $this->parseLegacyInteger($record['recipe_id']);
            $this->assertReference($recipeIds, $recipeId, "{$dataset} at index {$index} references missing recipe ID {$recipeId}.");
        }
    }

    /**
     * @param array<int, array<string, mixed>> $records
     * @return array<int, true>
     */
    private function legacyIds(array $records, string $dataset): array
    {
        $ids = [];
        foreach ($records as $index => $record) {
            if (! array_key_exists('id', $record)) {
                throw new InvalidArgumentException(ucfirst($dataset) . " at index {$index} is missing an ID.");
            }

            $id = $this->parseLegacyInteger($record['id']);
            if (isset($ids[$id])) {
                throw new InvalidArgumentException("Duplicate {$dataset} ID {$id} at index {$index}.");
            }

            $ids[$id] = true;
        }

        return $ids;
    }

    /** @param array<int, true> $references */
    private function assertReference(array $references, int $id, string $message): void
    {
        if (! isset($references[$id])) {
            throw new InvalidArgumentException($message);
        }
    }

    private function orderFieldLabel(string $field): string
    {
        return match ($field) {
            'customer_email' => 'a customer email',
            'customer_name' => 'a customer name',
            'order_number' => 'an order number',
            default => "a {$field}",
        };
    }

    private function orderItemFieldLabel(string $field): string
    {
        return match ($field) {
            'order_id' => 'order ID',
            'product_name' => 'product name',
            'quantity' => 'quantity',
            'unit_price' => 'unit price',
            default => $field,
        };
    }

    private function couponType(mixed $value): string
    {
        $normalized = Str::lower($this->stringValue($value));
        $normalized = $normalized === 'fixed_amount' ? CouponType::Fixed->value : $normalized;
        $type = CouponType::tryFrom($normalized);
        throw_if($type === null, InvalidArgumentException::class, "Unsupported coupon type [{$normalized}].");

        return $type->value;
    }

    private function orderStatus(mixed $value): string
    {
        $normalized = Str::lower($this->stringValue($value));
        $status = OrderStatus::tryFrom($normalized);
        throw_if($status === null, InvalidArgumentException::class, "Unsupported order status [{$normalized}].");

        return $status->value;
    }

    private function paymentStatus(mixed $value): string
    {
        $normalized = Str::lower($this->stringValue($value));
        $status = PaymentStatus::tryFrom($normalized);
        throw_if($status === null, InvalidArgumentException::class, "Unsupported payment status [{$normalized}].");

        return $status->value;
    }

    private function paymentMethod(mixed $value): string
    {
        $normalized = Str::lower($this->stringValue($value));
        $method = PaymentMethod::tryFrom($normalized);
        throw_if($method === null, InvalidArgumentException::class, "Unsupported payment method [{$normalized}].");

        return $method->value;
    }

    private function deliveryType(mixed $value): string
    {
        $normalized = Str::lower($this->stringValue($value));
        $type = DeliveryType::tryFrom($normalized);
        throw_if($type === null, InvalidArgumentException::class, "Unsupported fulfillment type [{$normalized}].");

        return $type->value;
    }

    private function stringValue(mixed $value): string
    {
        if (! is_string($value) && ! is_int($value) && ! is_float($value)) {
            throw new \UnexpectedValueException('Expected a string-compatible legacy value.');
        }

        return (string) $value;
    }

    private function parseLegacyInteger(mixed $value): int
    {
        if (is_int($value)) {
            return $value;
        }

        if (! is_string($value) || filter_var($value, FILTER_VALIDATE_INT) === false) {
            throw new \UnexpectedValueException('Expected an integer-compatible legacy value.');
        }

        return (int) $value;
    }

    private function floatValue(mixed $value): float
    {
        if (is_float($value) || is_int($value)) {
            return $value;
        }

        if (! is_string($value) || ! is_numeric($value)) {
            throw new \UnexpectedValueException('Expected a numeric legacy value.');
        }

        return (float) $value;
    }
}
