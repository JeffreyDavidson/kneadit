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
        /** @var list<array{product_id: int, quantity: int}> $normalized */
        $normalized = $this->normalize($items, 'product_id');

        return $normalized;
    }

    /**
     * @param array<mixed> $items
     * @return list<array{order_item_id: int, quantity: int}>
     */
    public function orderItems(array $items): array
    {
        /** @var list<array{order_item_id: int, quantity: int}> $normalized */
        $normalized = $this->normalize($items, 'order_item_id');

        return $normalized;
    }

    /**
     * @param array<mixed> $items
     * @return list<array{product_id?: int, order_item_id?: int, quantity: int}>
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
