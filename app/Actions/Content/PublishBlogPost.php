<?php

namespace App\Actions\Content;

use App\Models\Content\TenantBlogPost;

class PublishBlogPost
{
    public function __invoke(TenantBlogPost $post): void
    {
        $post->update([
            'is_published' => true,
            'published_at' => now(),
        ]);
    }
}
