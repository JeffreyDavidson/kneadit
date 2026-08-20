<?php

namespace App\DataTransferObjects\Inventory;

final readonly class ShoppingListItem
{
    public function __construct(
        public int $ingredientId,
        public string $name,
        public string $unit,
        public float $currentStock,
        public float $needed,
        public float $unitPrice,
        public float $subtotal,
        public ?string $sku,
        public ?float $minimumOrder,
        public ?int $leadTimeDays,
    ) {}

    /**
     * @return array{ingredient_id: int, name: string, unit: string, current_stock: float, needed: float, unit_price: float, subtotal: float, sku: ?string, minimum_order: ?float, lead_time_days: ?int}
     */
    public function toArray(): array
    {
        return [
            'ingredient_id' => $this->ingredientId,
            'name' => $this->name,
            'unit' => $this->unit,
            'current_stock' => $this->currentStock,
            'needed' => $this->needed,
            'unit_price' => $this->unitPrice,
            'subtotal' => $this->subtotal,
            'sku' => $this->sku,
            'minimum_order' => $this->minimumOrder,
            'lead_time_days' => $this->leadTimeDays,
        ];
    }
}
