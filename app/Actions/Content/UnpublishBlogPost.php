<?php

declare(strict_types=1);

namespace App\Actions\Content;

use App\Models\Content\TenantBlogPost;

class UnpublishBlogPost
{
    public function __invoke(TenantBlogPost $post): void
    {
        $post->update([
            'is_published' => false,
            'published_at' => null,
        ]);
    }
}
