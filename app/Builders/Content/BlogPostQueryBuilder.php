<?php

namespace App\Builders\Content;

use App\Enums\Content\BlogPostCategory;
use App\Models\Content\BlogPost;
use Illuminate\Database\Eloquent\Builder;

/**
 * @template TModel of BlogPost
 *
 * @extends Builder<TModel>
 */
class BlogPostQueryBuilder extends Builder
{
    public function published(): static
    {
        $this->where('is_published', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->whereNotNull('slug')
            ->where('slug', '!=', '');

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

    public function forListing(?string $category = null): static
    {
        $this->published()->latest('published_at');

        if ($category && $category !== 'all') {
            $enum = BlogPostCategory::tryFrom($category);
            if ($enum) {
                $this->inCategory($enum);
            }
        }

        return $this;
    }
}
