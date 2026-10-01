<?php

declare(strict_types=1);

namespace App\Services\Inventory;

use App\DataTransferObjects\Inventory\IngredientDemandItem;

final readonly class IngredientDemandCalculator
{
    public function __construct(private RecipeLineConverter $converter) {}

    /**
     * @param  iterable<IngredientDemandItem>  $items
     * @return list<string>
     */
    public function shortages(iterable $items): array
    {
        $byIngredientId = [];

        foreach ($items as $item) {
            foreach ($item->product->recipes as $recipe) {
                foreach ($recipe->inventoryIngredients as $ingredient) {
                    $perUnit = $this->converter->inStockUnit($recipe, $ingredient);

                    if ($perUnit === null) {
                        continue;
                    }

                    $draw = $perUnit * $item->quantity;
                    $existing = $byIngredientId[$ingredient->id] ?? null;

                    $byIngredientId[$ingredient->id] = [
                        'name' => $ingredient->name,
                        'demand' => ($existing['demand'] ?? 0.0) + $draw,
                        'available' => (float) $ingredient->current_stock,
                    ];
                }
            }
        }

        $shortages = [];

        foreach ($byIngredientId as $row) {
            // Stock is held to 4 decimals, so demand is compared at that precision too.
            if (round($row['demand'], 4) > $row['available']) {
                $shortages[] = $row['name'];
            }
        }

        return $shortages;
    }
}
