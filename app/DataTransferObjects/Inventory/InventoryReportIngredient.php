<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Inventory;

use App\ValueObjects\Money;

final readonly class InventoryReportIngredient
{
    public function __construct(
        public string $name,
        public string $unit,
        public float $currentStock,
        public float $lowStockThreshold,
        public bool $isLow,
        public bool $isOut,
        public float $dailyUsage,
        public float $dailyDepletion,
        public ?float $daysUntilStockout,
        public Money $costPerUnit,
    ) {}

    /** @return array{name: string, unit: string, current_stock: float, low_stock_threshold: float, is_low: bool, is_out: bool, daily_usage: float, daily_depletion: float, days_until_stockout: float|null, cost_per_unit: float} */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'unit' => $this->unit,
            'current_stock' => $this->currentStock,
            'low_stock_threshold' => $this->lowStockThreshold,
            'is_low' => $this->isLow,
            'is_out' => $this->isOut,
            'daily_usage' => $this->dailyUsage,
            'daily_depletion' => $this->dailyDepletion,
            'days_until_stockout' => $this->daysUntilStockout,
            'cost_per_unit' => $this->costPerUnit->dollars(),
        ];
    }
}
