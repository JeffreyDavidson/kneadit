<?php

namespace App\DataTransferObjects\Inventory;

use Illuminate\Support\Collection;

final readonly class SupplierShoppingList
{
    /** @param Collection<int, ShoppingListItem> $items */
    public function __construct(
        public SupplierSummary $supplier,
        public Collection $items,
    ) {}

    public function total(): float
    {
        return round($this->items->sum(
            fn (ShoppingListItem $item): float => $item->subtotal,
        ), 2);
    }

    public function maximumLeadTimeDays(): int
    {
        return max(3, $this->items->max(
            fn (ShoppingListItem $item): int => $item->leadTimeDays ?? 0,
        ) ?? 0);
    }

    public function canRequestPurchaseOrder(): bool
    {
        return $this->supplier->id !== null
            && filled($this->supplier->email);
    }

    /**
     * @return array{supplier: array{id: ?int, name: string, email: ?string, phone: ?string}, items: array<int, array{ingredient_id: int, name: string, unit: string, current_stock: float, needed: float, unit_price: float, subtotal: float, sku: ?string, minimum_order: ?float, lead_time_days: ?int}>, total: float}
     */
    public function toArray(): array
    {
        return [
            'supplier' => $this->supplier->toArray(),
            'items' => $this->items
                ->map(fn (ShoppingListItem $item): array => $item->toArray())
                ->values()
                ->all(),
            'total' => $this->total(),
        ];
    }
}
