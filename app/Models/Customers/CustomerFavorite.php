<?php

namespace App\Models\Customers;

use App\Models\Inventory\Product;
use Database\Factories\Customers\CustomerFavoriteFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read Product|null $product
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerFavorite forCustomer(string $email)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerFavorite forProduct(int $productId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerFavorite newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerFavorite newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerFavorite query()
 *
 * @mixin \Eloquent
 */
#[Fillable('customer_email', 'product_id')]
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

    /** @param Builder<CustomerFavorite> $query */
    #[Scope]
    protected function forCustomer(Builder $query, string $email): void
    {
        $query->where('customer_email', $email);
    }

    /** @param Builder<CustomerFavorite> $query */
    #[Scope]
    protected function forProduct(Builder $query, int $productId): void
    {
        $query->where('product_id', $productId);
    }

    protected static function newFactory(): CustomerFavoriteFactory
    {
        return CustomerFavoriteFactory::new();
    }
}
