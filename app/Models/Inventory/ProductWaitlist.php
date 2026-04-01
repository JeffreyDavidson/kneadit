<?php

namespace App\Models\Inventory;

use Database\Factories\Inventory\ProductWaitlistFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read Product|null $product
 *
 * @method static \Database\Factories\ProductWaitlistFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductWaitlist newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductWaitlist newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductWaitlist query()
 *
 * @mixin \Eloquent
 */
class ProductWaitlist extends Model
{
    /** @use HasFactory<ProductWaitlistFactory> */
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'product_id',
        'customer_email',
        'customer_name',
        'notified_at',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'notified_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    protected $table = 'product_waitlists';

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    protected static function newFactory(): ProductWaitlistFactory
    {
        return ProductWaitlistFactory::new();
    }
}
