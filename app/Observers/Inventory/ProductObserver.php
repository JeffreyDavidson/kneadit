<?php

declare(strict_types=1);

namespace App\Observers\Inventory;

use App\Events\Customers\ProductReactivated;
use App\Models\Inventory\Product;

class ProductObserver
{
    public function updated(Product $product): void
    {
        if (! $product->wasChanged('is_active') || ! $product->is_active) {
            return;
        }

        event(new ProductReactivated($product));
    }
}
