<?php

namespace App\Models\Engagement;

use App\Builders\Customers\ReviewQueryBuilder;
use App\Casts\StripTagsCast;
use App\Models\Inventory\Product;
use App\Models\Orders\Order;
use App\Observers\LogsActivityObserver;
use Database\Factories\Engagement\ReviewFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\UseEloquentBuilder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read Order|null $order
 * @property-read Product|null $product
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Review newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Review newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Review query()
 *
 * @property-read int|null $count
 * @property-read float|null $avg_rating
 *
 * @mixin \Eloquent
 */
#[Fillable('customer_name', 'customer_email', 'product_id', 'order_id', 'rating', 'comment', 'photo_path', 'is_approved', 'is_featured')]
#[ObservedBy(LogsActivityObserver::class)]
#[UseEloquentBuilder(ReviewQueryBuilder::class)]
class Review extends Model
{
    /** @use HasFactory<ReviewFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'is_approved' => 'boolean',
            'is_featured' => 'boolean',
            'comment' => StripTagsCast::class,
        ];
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * @return BelongsTo<Order, $this>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    protected static function newFactory(): ReviewFactory
    {
        return ReviewFactory::new();
    }
}
