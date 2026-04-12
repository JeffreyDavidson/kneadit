<?php

namespace App\Models\Customers;

use App\Casts\StripTagsCast;
use App\Models\Inventory\Product;
use Database\Factories\Customers\CustomerPhotoFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read Product|null $product
 *
 * @method static Builder<static>|CustomerPhoto approved()
 * @method static \Database\Factories\CustomerPhotoFactory factory($count = null, $state = [])
 * @method static Builder<static>|CustomerPhoto featured()
 * @method static Builder<static>|CustomerPhoto newModelQuery()
 * @method static Builder<static>|CustomerPhoto newQuery()
 * @method static Builder<static>|CustomerPhoto query()
 *
 * @mixin \Eloquent
 */
#[Fillable('customer_name', 'customer_email', 'caption', 'photo_path', 'product_id', 'is_approved', 'is_featured')]
class CustomerPhoto extends Model
{
    /** @use HasFactory<CustomerPhotoFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'is_approved' => 'boolean',
            'is_featured' => 'boolean',
            'customer_name' => StripTagsCast::class,
            'caption' => StripTagsCast::class,
        ];
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /** @param Builder<CustomerPhoto> $query */
    #[Scope]
    protected function approved(Builder $query): void
    {
        $query->where('is_approved', true);
    }

    /** @param Builder<CustomerPhoto> $query */
    #[Scope]
    protected function featured(Builder $query): void
    {
        $query->where('is_featured', true);
    }

    /** @param Builder<CustomerPhoto> $query */
    #[Scope]
    protected function withCaptionLike(Builder $query, string $term): void
    {
        $query->whereLike('caption', "%{$term}%");
    }

    protected static function newFactory(): CustomerPhotoFactory
    {
        return CustomerPhotoFactory::new();
    }
}
