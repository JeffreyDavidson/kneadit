<?php

namespace App\Actions\Content;

use App\Models\Inventory\Product;
use App\Models\Inventory\ProductImage;

class SyncProductPrimaryImage
{
    public function __invoke(int $productId): void
    {
        $images = ProductImage::query()->where('product_id', $productId)->orderBy('sort_order')->get();

        if ($images->isEmpty()) {
            return;
        }

        ProductImage::query()->where('product_id', $productId)->update(['is_primary' => false]);
        $images->first()->updateQuietly(['is_primary' => true]);

        $product = Product::query()->find($productId);
        if ($product) {
            $product->updateQuietly(['image' => $images->first()->path]);
        }
    }
}
