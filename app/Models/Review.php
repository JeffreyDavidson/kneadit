<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Database\Factories\ReviewFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read Order|null $order
 * @property-read Product|null $product
 *
 * @method static \Database\Factories\ReviewFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Review newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Review newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Review query()
 *
 * @property-read int|null $count
 * @property-read float|null $avg_rating
 *
 * @mixin \Eloquent
 */
class Review extends Model
{
    /** @use HasFactory<ReviewFactory> */
    use HasFactory;

    use LogsActivity;

    protected $fillable = [
        'customer_name',
        'customer_email',
        'product_id',
        'order_id',
        'rating',
        'comment',
        'photo_path',
        'is_approved',
        'is_featured',
    ];

    protected function casts(): array
    {
        return [
            'is_approved' => 'boolean',
            'is_featured' => 'boolean',
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

    /** @param Builder<Review> $query */
    #[Scope]
    protected function approved(Builder $query): void
    {
        $query->where('is_approved', true);
    }
}
