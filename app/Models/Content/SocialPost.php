<?php

namespace App\Models\Content;

use App\Enums\Marketing\SocialPlatform;
use App\Enums\Marketing\SocialPostStatus;
use App\Models\Inventory\Product;
use Database\Factories\Content\SocialPostFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property SocialPlatform $platform
 * @property SocialPostStatus $status
 * @property-read Product|null $product
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialPost newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialPost newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialPost query()
 *
 * @property Carbon|null $scheduled_for
 *
 * @mixin \Eloquent
 */
#[Fillable('platform', 'caption', 'product_id', 'image_path', 'scheduled_for', 'status', 'notes')]
class SocialPost extends Model
{
    /** @use HasFactory<SocialPostFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'platform' => SocialPlatform::class,
            'status' => SocialPostStatus::class,
            'scheduled_for' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    protected static function newFactory(): SocialPostFactory
    {
        return SocialPostFactory::new();
    }
}
