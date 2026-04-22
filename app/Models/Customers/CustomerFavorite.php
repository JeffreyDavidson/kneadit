<?php

namespace App\Models\Customers;

use App\Builders\Customers\CustomerFavoriteQueryBuilder;
use App\Models\Inventory\Product;
use Database\Factories\Customers\CustomerFavoriteFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseEloquentBuilder;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read Product|null $product
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerFavorite newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerFavorite newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerFavorite query()
 *
 * @mixin \Eloquent
 */
#[Fillable('customer_email', 'product_id')]
#[UseEloquentBuilder(CustomerFavoriteQueryBuilder::class)]
#[UseFactory(CustomerFavoriteFactory::class)]
class CustomerFavorite extends Model
{
    /** @use HasFactory<CustomerFavoriteFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
