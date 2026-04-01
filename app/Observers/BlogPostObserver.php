<?php

namespace App\Observers;

use App\Actions\Content\GenerateUniqueSlug;
use App\Models\BlogPost;
use Illuminate\Support\Str;

class BlogPostObserver
{
    public function __construct(
        private GenerateUniqueSlug $generateSlug,
    ) {}

    public function creating(BlogPost $post): void
    {
        if (empty($post->slug)) {
            $post->slug = ($this->generateSlug)(BlogPost::class, $post->title);
        }
    }

    public function updating(BlogPost $post): void
    {
        if ($post->isDirty('title')) {
            $post->slug = Str::slug($post->title);
        }
    }
}
