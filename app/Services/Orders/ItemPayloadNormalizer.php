<?php

namespace App\Services\Orders;

final class ItemPayloadNormalizer
{
    /**
     * @param array<mixed> $items
     * @return list<array{product_id: int, quantity: int}>
     */
    public function products(array $items): array
    {
        return array_map(
            fn (array $item): array => [
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
            ],
            $this->normalize($items, 'product_id'),
        );
    }

    /**
     * @param array<mixed> $items
     * @return list<array{order_item_id: int, quantity: int}>
     */
    public function orderItems(array $items): array
    {
        return array_map(
            fn (array $item): array => [
                'order_item_id' => $item['order_item_id'],
                'quantity' => $item['quantity'],
            ],
            $this->normalize($items, 'order_item_id'),
        );
    }

    /**
     * @param array<mixed> $items
     * @return list<array<string, int>>
     */
    private function normalize(array $items, string $identifierKey): array
    {
        $normalized = [];

        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }

            $identifier = $item[$identifierKey] ?? null;
            $quantity = $item['quantity'] ?? null;

            if (is_int($identifier) && is_int($quantity)) {
                $normalized[] = [
                    $identifierKey => $identifier,
                    'quantity' => $quantity,
                ];
            }
        }

        return $normalized;
    }
}
