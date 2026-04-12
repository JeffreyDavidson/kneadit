<?php

namespace App\Models\Content;

use App\Builders\Content\BlogPostQueryBuilder;
use App\Enums\Content\BlogPostCategory;
use App\Observers\Content\BlogPostObserver;
use Database\Factories\Content\BlogPostFactory;
use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\UseEloquentBuilder;
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
#[Connection('central')]
#[Fillable('title', 'slug', 'excerpt', 'body', 'featured_image', 'category', 'meta_title', 'meta_description', 'is_published', 'published_at')]
#[ObservedBy(BlogPostObserver::class)]
#[UseEloquentBuilder(BlogPostQueryBuilder::class)]
class BlogPost extends Model
{
    /** @use HasFactory<BlogPostFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'published_at' => 'datetime',
            'category' => BlogPostCategory::class,
        ];
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

    public function resolveRouteBinding(mixed $value, mixed $field = null): ?self
    {
        return static::query()
            ->where($field ?? $this->getRouteKeyName(), $value)
            ->published()
            ->first();
    }

    protected static function newFactory(): BlogPostFactory
    {
        return BlogPostFactory::new();
    }
}
