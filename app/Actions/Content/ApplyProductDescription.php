<?php

namespace App\Actions\Content;

use App\Models\Inventory\Product;

class ApplyProductDescription
{
    public function __invoke(Product $product, string $description): void
    {
        $product->update(['description' => $description]);
    }
}
