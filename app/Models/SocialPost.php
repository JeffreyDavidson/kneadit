<?php

namespace App\Models;

use App\Enums\SocialPostStatus;
use Database\Factories\SocialPostFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property SocialPostStatus $status
 * @property-read Product|null $product
 *
 * @method static \Database\Factories\SocialPostFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialPost newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialPost newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialPost query()
 *
 * @property Carbon|null $scheduled_for
 *
 * @mixin \Eloquent
 */
class SocialPost extends Model
{
    /** @use HasFactory<SocialPostFactory> */
    use HasFactory;

    protected $fillable = [
        'platform',
        'caption',
        'product_id',
        'image_path',
        'scheduled_for',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => SocialPostStatus::class,
            'scheduled_for' => 'datetime',
        ];
    }

    public const PLATFORMS = [
        'instagram' => 'Instagram',
        'facebook' => 'Facebook',
        'tiktok' => 'TikTok',
    ];

    public const PLATFORM_MAX_CHARS = [
        'instagram' => 2200,
        'facebook' => 63206,
        'tiktok' => 4000,
    ];

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
