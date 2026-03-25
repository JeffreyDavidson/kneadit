<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read Product|null $product
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductImage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductImage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductImage query()
 *
 * @mixin \Eloquent
 */
class ProductImage extends Model
{
    protected static function booted(): void
    {
        // After any save, ensure the lowest sort_order image is primary
        static::saved(function (ProductImage $image) {
            static::syncPrimary($image->product_id);
        });

        static::deleted(function (ProductImage $image) {
            static::syncPrimary($image->product_id);
        });
    }

    /**
     * Set the image with the lowest sort_order as primary.
     */
    public static function syncPrimary(int $productId): void
    {
        $images = static::query()->where('product_id', $productId)->orderBy('sort_order')->get();

        if ($images->isEmpty()) {
            return;
        }

        // Mark all as not primary, then set first as primary
        static::query()->where('product_id', $productId)->update(['is_primary' => false]);
        $images->first()->update(['is_primary' => true]);

        // Sync the legacy `image` field on the product
        $product = Product::query()->find($productId);
        if ($product) {
            $product->updateQuietly(['image' => $images->first()->path]);
        }
    }

    protected $fillable = [
        'product_id',
        'path',
        'sort_order',
        'is_primary',
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
