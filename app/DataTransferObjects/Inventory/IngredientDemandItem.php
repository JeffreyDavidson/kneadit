<?php

namespace App\DataTransferObjects\Inventory;

use App\Models\Inventory\Product;

final readonly class IngredientDemandItem
{
    public function __construct(
        public Product $product,
        public int $quantity,
    ) {}
}
