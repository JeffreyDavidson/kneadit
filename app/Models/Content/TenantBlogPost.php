<?php

namespace App\Models\Content;

use App\Builders\Content\TenantBlogPostQueryBuilder;
use Database\Factories\Content\TenantBlogPostFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseEloquentBuilder;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $title
 * @property string $slug
 * @property string|null $excerpt
 * @property string $body
 * @property string|null $featured_image
 * @property array<int, string>|null $tags
 * @property string|null $author_name
 * @property bool $is_published
 * @property \Illuminate\Support\Carbon|null $published_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TenantBlogPost newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TenantBlogPost newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TenantBlogPost query()
 *
 * @mixin \Eloquent
 */
#[Table('blog_posts')]
#[Fillable('title', 'slug', 'excerpt', 'body', 'featured_image', 'tags', 'author_name', 'is_published', 'published_at')]
#[UseEloquentBuilder(TenantBlogPostQueryBuilder::class)]
#[UseFactory(TenantBlogPostFactory::class)]
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
}
