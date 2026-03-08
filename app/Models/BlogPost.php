<?php

namespace App\Models;

use App\Traits\LogsActivity;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class BlogPost extends Model
{
    use LogsActivity;

    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'body',
        'featured_image',
        'is_published',
        'published_at',
        'author_name',
        'tags',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'published_at' => 'datetime',
        'tags' => 'array',
    ];

    protected static function booted(): void
    {
        static::saving(function (BlogPost $post) {
            if (empty($post->slug) || $post->isDirty('title')) {
                $post->slug = Str::slug($post->title);

                // Ensure uniqueness
                $original = $post->slug;
                $count = 1;
                while (static::where('slug', $post->slug)->where('id', '!=', $post->id)->exists()) {
                    $post->slug = $original . '-' . $count++;
                }
            }
        });
    }
}
