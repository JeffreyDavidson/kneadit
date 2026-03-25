<?php

namespace Database\Factories;

use App\Models\GalleryPhoto;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GalleryPhoto> */
class GalleryPhotoFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'image_path' => 'gallery/'.fake()->uuid().'.jpg',
            'category' => fake()->randomElement(['products', 'bakery', 'team', 'events', 'process', 'other']),
            'sort_order' => fake()->numberBetween(0, 100),
            'is_visible' => true,
        ];
    }
}
