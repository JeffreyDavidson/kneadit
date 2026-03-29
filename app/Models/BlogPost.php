<?php

namespace App\Models;

use App\Enums\BlogPostCategory;
use App\Observers\BlogPostObserver;
use Database\Factories\BlogPostFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property-read string $url
 *
 * @method static \Database\Factories\BlogPostFactory factory($count = null, $state = [])
 * @method static Builder<static>|BlogPost newModelQuery()
 * @method static Builder<static>|BlogPost newQuery()
 * @method static Builder<static>|BlogPost published()
 * @method static Builder<static>|BlogPost query()
 *
 * @property Carbon|null $published_at
 *
 * @mixin \Eloquent
 */
#[ObservedBy(BlogPostObserver::class)]
class BlogPost extends Model
{
    /** @use HasFactory<BlogPostFactory> */
    use HasFactory;

    protected $connection = 'central';

    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'body',
        'featured_image',
        'category',
        'meta_title',
        'meta_description',
        'is_published',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'published_at' => 'datetime',
            'category' => BlogPostCategory::class,
        ];
    }

    /** @param Builder<BlogPost> $query */
    #[Scope]
    protected function published(Builder $query): void
    {
        $query->where('is_published', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    /** @return Attribute<mixed, never> */
    protected function url(): Attribute
    {
        return Attribute::make(
            get: fn () => route('blog.show', $this->slug),
        );
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /** @param mixed $value */
    public function resolveRouteBinding($value, $field = null): ?self
    {
        return static::query()
            ->where($field ?? $this->getRouteKeyName(), $value)
            ->published()
            ->first();
    }
}
