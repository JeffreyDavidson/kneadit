<?php

namespace App\Models;

use Database\Factories\BlogPostFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

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
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (BlogPost $post) {
            if (empty($post->slug)) {
                $slug = Str::slug($post->title);
                $original = $slug;
                $i = 2;
                while (static::query()->where('slug', $slug)->exists()) {
                    $slug = $original.'-'.$i++;
                }
                $post->slug = $slug;
            }
        });

        static::updating(function (BlogPost $post) {
            if ($post->isDirty('title')) {
                $post->slug = Str::slug($post->title);
            }
        });
    }

    /** @param Builder<BlogPost> $query */
    #[Scope]
    protected function published(Builder $query): void
    {
        $query->where('is_published', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    protected function getUrlAttribute(): string
    {
        return route('blog.show', $this->slug);
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
