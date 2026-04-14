<?php

namespace App\Observers\Content;

use App\Actions\Content\SyncProductPrimaryImage;
use App\Models\Inventory\ProductImage;

class ProductImageObserver
{
    public function __construct(
        private SyncProductPrimaryImage $syncPrimaryImage,
    ) {}

    public function saved(ProductImage $image): void
    {
        ($this->syncPrimaryImage)($image->product_id);
    }

    public function deleted(ProductImage $image): void
    {
        ($this->syncPrimaryImage)($image->product_id);
    }
}
