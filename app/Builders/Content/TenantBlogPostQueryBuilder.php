<?php

namespace App\Builders\Content;

use App\Models\Content\TenantBlogPost;
use Illuminate\Database\Eloquent\Builder;

/** @extends Builder<TenantBlogPost> */
class TenantBlogPostQueryBuilder extends Builder
{
    public function published(): static
    {
        $this->where('is_published', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());

        return $this;
    }
}
