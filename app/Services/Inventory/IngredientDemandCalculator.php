<?php

namespace App\Services\Inventory;

use App\DataTransferObjects\Inventory\IngredientDemandItem;

final class IngredientDemandCalculator
{
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
                    /** @var object{quantity: string, unit: string} $pivot */
                    $pivot = $ingredient->pivot;
                    $draw = (float) $pivot->quantity * $item->quantity;
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
            if ($row['demand'] > $row['available']) {
                $shortages[] = $row['name'];
            }
        }

        return $shortages;
    }
}
