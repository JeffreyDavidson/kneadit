<?php

namespace App\Observers;

use App\Models\BlogPost;
use Illuminate\Support\Str;

class BlogPostObserver
{
    public function creating(BlogPost $post): void
    {
        if (empty($post->slug)) {
            $slug = Str::slug($post->title);
            $original = $slug;
            $i = 2;
            while (BlogPost::query()->where('slug', $slug)->exists()) {
                $slug = "{$original}-{$i}";
                $i++;
            }
            $post->slug = $slug;
        }
    }

    public function updating(BlogPost $post): void
    {
        if ($post->isDirty('title')) {
            $post->slug = Str::slug($post->title);
        }
    }
}
