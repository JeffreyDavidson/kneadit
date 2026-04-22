<?php

namespace App\Models\Customers;

use App\Builders\Customers\CustomerPhotoQueryBuilder;
use App\Casts\StripTagsCast;
use App\Models\Inventory\Product;
use Database\Factories\Customers\CustomerPhotoFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseEloquentBuilder;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read Product|null $product
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerPhoto newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerPhoto newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerPhoto query()
 *
 * @mixin \Eloquent
 */
#[Fillable('customer_name', 'customer_email', 'caption', 'photo_path', 'product_id', 'is_approved', 'is_featured')]
#[UseEloquentBuilder(CustomerPhotoQueryBuilder::class)]
#[UseFactory(CustomerPhotoFactory::class)]
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
}
