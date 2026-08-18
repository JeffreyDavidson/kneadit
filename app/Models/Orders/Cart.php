<?php

namespace App\Models\Orders;

use App\Builders\Orders\CartQueryBuilder;
use Database\Factories\Orders\CartFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseEloquentBuilder;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $cart_token
 * @property string|null $customer_email
 * @property string|null $customer_name
 * @property Carbon|null $last_activity_at
 * @property Carbon|null $expires_at
 * @property Carbon|null $recovery_sent_at
 * @property Carbon|null $converted_at
 * @property-read Collection<int, CartItem> $items
 *
 * @method static CartQueryBuilder|Cart newModelQuery()
 * @method static CartQueryBuilder|Cart newQuery()
 * @method static CartQueryBuilder|Cart query()
 *
 * @mixin \Eloquent
 */
#[Fillable('cart_token', 'customer_email', 'customer_name', 'last_activity_at', 'expires_at', 'recovery_sent_at', 'converted_at')]
#[UseEloquentBuilder(CartQueryBuilder::class)]
#[UseFactory(CartFactory::class)]
class Cart extends Model
{
    /** @use HasFactory<CartFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'last_activity_at' => 'datetime',
            'expires_at' => 'datetime',
            'recovery_sent_at' => 'datetime',
            'converted_at' => 'datetime',
        ];
    }

    /** @return HasMany<CartItem, $this> */
    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }
}
