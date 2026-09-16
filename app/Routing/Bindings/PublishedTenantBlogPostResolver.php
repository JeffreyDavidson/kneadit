<?php

namespace App\Routing\Bindings;

use App\Models\Content\TenantBlogPost;

final class PublishedTenantBlogPostResolver
{
    public function __invoke(string $slug): TenantBlogPost
    {
        return TenantBlogPost::query()
            ->published()
            ->where('slug', $slug)
            ->firstOrFail();
    }
}
