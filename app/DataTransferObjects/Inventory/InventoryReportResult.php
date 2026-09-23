<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Inventory;

use Illuminate\Contracts\Support\Arrayable;

/** @implements Arrayable<string, mixed> */
final readonly class InventoryReportResult implements Arrayable
{
    /**
     * @param  list<InventoryReportIngredient>  $ingredients
     */
    public function __construct(
        public array $ingredients,
        public int $totalItems,
        public int $lowStockItems,
        public int $outOfStockItems,
    ) {}

    /**
     * @return array{
     *     ingredients: list<array{
     *         name: string,
     *         unit: string,
     *         current_stock: float,
     *         low_stock_threshold: float,
     *         is_low: bool,
     *         is_out: bool,
     *         daily_usage: float,
     *         days_until_stockout: float|null,
     *         cost_per_unit: float
     *     }>,
     *     totalItems: int,
     *     lowStockItems: int,
     *     outOfStockItems: int
     * }
     */
    public function toArray(): array
    {
        return [
            'ingredients' => array_map(
                static fn (InventoryReportIngredient $ingredient): array => $ingredient->toArray(),
                $this->ingredients,
            ),
            'totalItems' => $this->totalItems,
            'lowStockItems' => $this->lowStockItems,
            'outOfStockItems' => $this->outOfStockItems,
        ];
    }
}
