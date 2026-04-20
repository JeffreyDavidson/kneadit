<?php

namespace Database\Factories\Content;

use App\Models\Content\GalleryPhoto;
use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GalleryPhoto>
 */
#[UseModel(GalleryPhoto::class)]
class GalleryPhotoFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'image_path' => 'gallery/' . fake()->uuid() . '.jpg',
            'category' => fake()->randomElement(['products', 'bakery', 'team', 'events', 'process', 'other']),
            'sort_order' => fake()->numberBetween(0, 100),
            'is_visible' => true,
        ];
    }

    /**
     * Photo is visible.
     */
    public function visible(): static
    {
        return $this->state(fn (array $attributes) => ['is_visible' => true]);
    }

    /**
     * Photo is hidden.
     */
    public function hidden(): static
    {
        return $this->state(fn (array $attributes) => ['is_visible' => false]);
    }
}
