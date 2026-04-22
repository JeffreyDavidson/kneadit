<?php

namespace App\Models\Content;

use App\Builders\Content\GalleryPhotoQueryBuilder;
use App\Enums\Content\GalleryCategory;
use Database\Factories\Content\GalleryPhotoFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseEloquentBuilder;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property GalleryCategory $category
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GalleryPhoto newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GalleryPhoto newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GalleryPhoto query()
 *
 * @mixin \Eloquent
 */
#[Fillable('title', 'image_path', 'category', 'sort_order', 'is_visible')]
#[UseEloquentBuilder(GalleryPhotoQueryBuilder::class)]
#[UseFactory(GalleryPhotoFactory::class)]
class GalleryPhoto extends Model
{
    /** @use HasFactory<GalleryPhotoFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'is_visible' => 'boolean',
            'sort_order' => 'integer',
            'category' => GalleryCategory::class,
        ];
    }
}
