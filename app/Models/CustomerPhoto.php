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
 * @method static Builder<static>|CustomerPhoto approved()
 * @method static \Database\Factories\CustomerPhotoFactory factory($count = null, $state = [])
 * @method static Builder<static>|CustomerPhoto featured()
 * @method static Builder<static>|CustomerPhoto newModelQuery()
 * @method static Builder<static>|CustomerPhoto newQuery()
 * @method static Builder<static>|CustomerPhoto query()
 *
 * @mixin \Eloquent
 */
class CustomerPhoto extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_name',
        'customer_email',
        'caption',
        'photo_path',
        'product_id',
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

    #[Scope]
    protected function approved(Builder $query): void
    {
        $query->where('is_approved', true);
    }

    #[Scope]
    protected function featured(Builder $query): void
    {
        $query->where('is_featured', true);
    }
}
