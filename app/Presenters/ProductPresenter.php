<?php

namespace App\Presenters;

use App\Models\Inventory\Product;
use App\Models\Inventory\SeasonalItem;
use Illuminate\Support\Facades\Storage;

final class ProductPresenter
{
    public function __construct(
        public readonly Product $product,
    ) {}

    public static function for(Product $product): self
    {
        $product->loadMissing(['primaryImage', 'seasonalItems']);

        return new self($product);
    }

    public function primaryImageUrl(): ?string
    {
        $this->product->loadMissing('primaryImage');

        if ($primary = $this->product->primaryImage) {
            return Storage::disk('public')->url($primary->path);
        }

        if ($this->product->image) {
            return Storage::disk('public')->url($this->product->image);
        }

        return null;
    }

    public function isInSeason(): bool
    {
        $this->product->loadMissing('seasonalItems');

        if ($this->product->seasonalItems->isEmpty()) {
            return true;
        }

        return $this->product->seasonalItems->contains(
            fn (SeasonalItem $item) => $item->is_currently_available,
        );
    }

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
