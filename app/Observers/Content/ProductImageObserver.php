<?php

namespace App\Observers\Content;

use App\Actions\Content\SyncProductPrimaryImage;
use App\Models\Inventory\ProductImage;

class ProductImageObserver
{
    public function saved(ProductImage $image): void
    {
        app(SyncProductPrimaryImage::class)($image->product_id);
    }

    public function deleted(ProductImage $image): void
    {
        app(SyncProductPrimaryImage::class)($image->product_id);
    }
}
