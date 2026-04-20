<?php

namespace App\Models\Orders;

use App\Casts\MoneyCast;
use App\Models\Inventory\Product;
use App\ValueObjects\Money;
use Database\Factories\Orders\OrderItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read Money $total_price
 * @property-read Order|null $order
 * @property-read Product|null $product
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem query()
 *
 * @property-read int|null $units_sold
 * @property-read float|null $revenue
 * @property Money $unit_price
 *
 * @mixin \Eloquent
 */
#[Fillable('order_id', 'product_id', 'quantity', 'unit_price', 'special_instructions')]
class OrderItem extends Model
{
    /** @use HasFactory<OrderItemFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_price' => MoneyCast::class,
        ];
    }

    /**
     * @return BelongsTo<Order, $this>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /** @return Attribute<Money, never> */
    protected function totalPrice(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->unit_price->multiply($this->quantity),
        );
    }

    protected static function newFactory(): OrderItemFactory
    {
        return OrderItemFactory::new();
    }
}
