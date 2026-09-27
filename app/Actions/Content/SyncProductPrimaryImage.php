<?php

namespace App\Actions\Content;

use App\Models\Inventory\Product;
use App\Models\Inventory\ProductImage;

class SyncProductPrimaryImage
{
    public function __invoke(int $productId): void
    {
        $image = ProductImage::query()
            ->where('product_id', $productId)
            ->orderBy('sort_order')
            ->first();

        if (! $image) {
            return;
        }

        ProductImage::query()->where('product_id', $productId)->update(['is_primary' => false]);
        $image->updateQuietly(['is_primary' => true]);

        $product = Product::query()->find($productId);
        if ($product) {
            $product->updateQuietly(['image' => $image->path]);
        }
    }
}
