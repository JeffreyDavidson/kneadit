<?php

namespace App\Builders;

use App\Enums\BlogPostCategory;
use App\Models\BlogPost;
use Illuminate\Database\Eloquent\Builder;

/** @extends Builder<BlogPost> */
class BlogPostQueryBuilder extends Builder
{
    public function published(): static
    {
        $this->where('is_published', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());

        return $this;
    }

    public function inCategory(BlogPostCategory $category): static
    {
        $this->where('category', $category);

        return $this;
    }

    public function relatedTo(BlogPost $post, int $limit = 3): static
    {
        $this->where('id', '!=', $post->id)
            ->where('category', $post->category)
            ->limit($limit);

        return $this;
    }
}
