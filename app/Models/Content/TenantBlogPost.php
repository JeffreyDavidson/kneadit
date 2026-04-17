<?php

namespace App\Models\Content;

use Database\Factories\Content\TenantBlogPostFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TenantBlogPost newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TenantBlogPost newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TenantBlogPost query()
 *
 * @mixin \Eloquent
 */
#[Table('blog_posts')]
#[Fillable('title', 'slug', 'excerpt', 'body', 'featured_image', 'tags', 'author_name', 'is_published', 'published_at')]
class TenantBlogPost extends Model
{
    /** @use HasFactory<TenantBlogPostFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'published_at' => 'datetime',
            'tags' => 'array',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function resolveRouteBinding(mixed $value, mixed $field = null): ?self
    {
        return $this->newQuery()
            ->published()
            ->where($field ?? $this->getRouteKeyName(), $value)
            ->firstOrFail();
    }

    /** @param Builder<TenantBlogPost> $query */
    #[Scope]
    protected function published(Builder $query): void
    {
        $query->where('is_published', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    protected static function newFactory(): TenantBlogPostFactory
    {
        return TenantBlogPostFactory::new();
    }
}
