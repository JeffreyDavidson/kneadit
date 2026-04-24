<?php

namespace App\Exceptions\Inventory;

use App\Models\Inventory\Ingredient;
use Illuminate\Contracts\Debug\ShouldntReport;
use RuntimeException;

/**
 * Last-resort guard. Thrown when a stock adjustment would push an ingredient's
 * current_stock below zero. Callers (placement, modification) are expected to
 * validate beforehand — this exception means a path bypassed validation.
 */
class StockWouldGoNegativeException extends RuntimeException implements ShouldntReport
{
    public function __construct(
        public readonly Ingredient $ingredient,
        public readonly float $delta,
        public readonly float $resultingStock,
    ) {
        parent::__construct(sprintf(
            'Stock adjustment for %s would result in negative stock: %.2f + %.2f = %.2f',
            $ingredient->name,
            (float) $ingredient->current_stock,
            $delta,
            $resultingStock,
        ));
    }

    /**
     * @return array<string, mixed>
     */
    public function context(): array
    {
        return [
            'ingredient_id' => $this->ingredient->id,
            'ingredient_name' => $this->ingredient->name,
            'current_stock' => (float) $this->ingredient->current_stock,
            'delta' => $this->delta,
            'resulting_stock' => $this->resultingStock,
        ];
    }
}
