<?php

namespace App\Routing\Resolvers;

use App\Models\Content\BlogPost;

final class PublishedBlogPostResolver
{
    public function __invoke(string $slug): BlogPost
    {
        return BlogPost::query()
            ->where('slug', $slug)
            ->published()
            ->firstOrFail();
    }
}
