<?php

namespace App\Models\Engagement;

use App\Models\Inventory\Product;
use Database\Factories\Engagement\PageViewFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\WithoutTimestamps;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read Product|null $product
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PageView newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PageView newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PageView query()
 *
 * @property-read int|null $views
 *
 * @mixin \Eloquent
 */
#[WithoutTimestamps]
#[Fillable('page', 'product_id', 'session_id', 'ip_address', 'user_agent', 'created_at')]
class PageView extends Model
{
    /** @use HasFactory<PageViewFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    protected static function newFactory(): PageViewFactory
    {
        return PageViewFactory::new();
    }
}
