<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class BlogPost extends Model
{
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

    protected $casts = [
        'is_published' => 'boolean',
        'published_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (BlogPost $post) {
            if (empty($post->slug)) {
                $slug = Str::slug($post->title);
                $original = $slug;
                $i = 2;
                while (static::where('slug', $slug)->exists()) {
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

    public function scopePublished($query)
    {
        return $query->where('is_published', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function getUrlAttribute(): string
    {
        return route('blog.show', $this->slug);
    }
}
