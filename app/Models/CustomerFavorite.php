<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read Product|null $product
 *
 * @method static \Database\Factories\CustomerFavoriteFactory factory($count = null, $state = [])
 * @method static Builder<static>|CustomerFavorite forCustomer(string $email)
 * @method static Builder<static>|CustomerFavorite newModelQuery()
 * @method static Builder<static>|CustomerFavorite newQuery()
 * @method static Builder<static>|CustomerFavorite query()
 *
 * @mixin \Eloquent
 */
class CustomerFavorite extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_email',
        'product_id',
    ];

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    #[Scope]
    protected function forCustomer(Builder $query, string $email): void
    {
        $query->where('customer_email', $email);
    }

    public static function toggle(string $email, int $productId): bool
    {
        $favorite = static::query()->where('customer_email', $email)
            ->where('product_id', $productId)
            ->first();

        if ($favorite) {
            $favorite->delete();

            return false; // removed
        } else {
            static::query()->create([
                'customer_email' => $email,
                'product_id' => $productId,
            ]);

            return true; // added
        }
    }

    public static function isFavorite(string $email, int $productId): bool
    {
        return static::query()->where('customer_email', $email)
            ->where('product_id', $productId)
            ->exists();
    }
}
