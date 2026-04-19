<?php

namespace Database\Factories\Content;

use App\Models\Content\TenantBlogPost;
use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<TenantBlogPost> */
#[UseModel(TenantBlogPost::class)]
class TenantBlogPostFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        $title = fake()->sentence(5);

        return [
            'title' => $title,
            'slug' => Str::slug($title),
            'excerpt' => fake()->paragraph(),
            'body' => fake()->paragraphs(3, true),
            'featured_image' => null,
            'tags' => null,
            'author_name' => fake()->name(),
            'is_published' => false,
            'published_at' => null,
        ];
    }

    public function draft(): static
    {
        return $this->state([
            'is_published' => false,
            'published_at' => null,
        ]);
    }

    public function published(): static
    {
        return $this->state([
            'is_published' => true,
            'published_at' => now(),
        ]);
    }
}
