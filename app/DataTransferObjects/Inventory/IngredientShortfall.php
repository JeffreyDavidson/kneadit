<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Inventory;

use App\Models\Inventory\Ingredient;

/**
 * An ingredient left below zero after an order drew on it, and by how much.
 */
final readonly class IngredientShortfall
{
    public function __construct(
        public Ingredient $ingredient,
        public float $shortfall,
    ) {}
}
