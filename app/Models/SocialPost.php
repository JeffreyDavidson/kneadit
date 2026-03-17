<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SocialPost extends Model
{
    protected $fillable = [
        'platform',
        'caption',
        'product_id',
        'image_path',
        'scheduled_for',
        'status',
        'notes',
    ];

    protected $casts = [
        'scheduled_for' => 'datetime',
    ];

    public const PLATFORMS = [
        'instagram' => 'Instagram',
        'facebook' => 'Facebook',
        'tiktok' => 'TikTok',
    ];

    public const STATUSES = [
        'draft' => 'Draft',
        'scheduled' => 'Scheduled',
        'posted' => 'Posted',
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
