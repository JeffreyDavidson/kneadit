<?php

namespace App\Models\Content;

use App\Enums\Content\GalleryCategory;
use Database\Factories\Content\GalleryPhotoFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property GalleryCategory $category
 *
 * @method static \Database\Factories\GalleryPhotoFactory factory($count = null, $state = [])
 * @method static Builder<static>|GalleryPhoto newModelQuery()
 * @method static Builder<static>|GalleryPhoto newQuery()
 * @method static Builder<static>|GalleryPhoto ordered()
 * @method static Builder<static>|GalleryPhoto query()
 * @method static Builder<static>|GalleryPhoto visible()
 *
 * @mixin \Eloquent
 */
class GalleryPhoto extends Model
{
    /** @use HasFactory<GalleryPhotoFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'image_path',
        'category',
        'sort_order',
        'is_visible',
    ];

    protected function casts(): array
    {
        return [
            'is_visible' => 'boolean',
            'sort_order' => 'integer',
            'category' => GalleryCategory::class,
        ];
    }

    /** @param Builder<GalleryPhoto> $query */
    #[Scope]
    protected function visible(Builder $query): void
    {
        $query->where('is_visible', true);
    }

    /** @param Builder<GalleryPhoto> $query */
    #[Scope]
    protected function ordered(Builder $query): void
    {
        $query->orderBy('sort_order');
    }

    protected static function newFactory(): GalleryPhotoFactory
    {
        return GalleryPhotoFactory::new();
    }
}
