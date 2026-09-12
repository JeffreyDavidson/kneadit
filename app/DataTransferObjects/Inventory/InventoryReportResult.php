<?php

namespace App\DataTransferObjects\Inventory;

use App\ValueObjects\Money;
use Illuminate\Contracts\Support\Arrayable;

/** @implements Arrayable<string, mixed> */
final readonly class InventoryReportResult implements Arrayable
{
    /**
     * @param list<array{
     *     name: string,
     *     unit: string,
     *     current_stock: float,
     *     low_stock_threshold: float,
     *     is_low: bool,
     *     is_out: bool,
     *     daily_usage: float,
     *     days_until_stockout: float|null,
     *     cost_per_unit: Money
     * }> $ingredients
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
            'ingredients' => array_map(static fn (array $ingredient): array => [
                'name' => $ingredient['name'],
                'unit' => $ingredient['unit'],
                'current_stock' => $ingredient['current_stock'],
                'low_stock_threshold' => $ingredient['low_stock_threshold'],
                'is_low' => $ingredient['is_low'],
                'is_out' => $ingredient['is_out'],
                'daily_usage' => $ingredient['daily_usage'],
                'days_until_stockout' => $ingredient['days_until_stockout'],
                'cost_per_unit' => $ingredient['cost_per_unit']->dollars(),
            ], $this->ingredients),
            'totalItems' => $this->totalItems,
            'lowStockItems' => $this->lowStockItems,
            'outOfStockItems' => $this->outOfStockItems,
        ];
    }
}
