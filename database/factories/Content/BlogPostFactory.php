<?php

namespace Database\Factories\Content;

use App\Enums\Content\BlogPostCategory;
use App\Models\Content\BlogPost;
use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<BlogPost>
 */
#[UseModel(BlogPost::class)]
class BlogPostFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->sentence(5);

        return [
            'title' => $title,
            'slug' => Str::slug($title),
            'excerpt' => fake()->paragraph(),
            'body' => fake()->paragraphs(5, true),
            'featured_image' => null,
            'is_published' => false,
            'published_at' => null,
        ];
    }

    /**
     * Post is a draft (not published).
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_published' => false,
            'published_at' => null,
        ]);
    }

    /**
     * Post is published.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_published' => true,
            'published_at' => now(),
        ]);
    }

    /**
     * Post is in a specific category.
     */
    public function inCategory(BlogPostCategory $category): static
    {
        return $this->state(fn (array $attributes) => ['category' => $category]);
    }
}
