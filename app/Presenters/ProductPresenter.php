<?php

namespace App\Presenters;

use App\Models\Inventory\Product;

class ProductPresenter
{
    public function __construct(
        public readonly Product $product,
    ) {}

    public function seasonalBadge(): ?string
    {
        $this->product->loadMissing('seasonalItems');

        $seasonal = $this->product->seasonalItems->first();

        if (! $seasonal) {
            return null;
        }

        if ($seasonal->is_currently_available) {
            return 'Limited Time';
        }

        return "Available {$seasonal->available_from?->format('M')} - {$seasonal->available_until?->format('M')}";
    }
}
