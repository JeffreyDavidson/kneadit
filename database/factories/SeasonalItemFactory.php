<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\SeasonalItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SeasonalItem> */
class SeasonalItemFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'available_from' => now()->subMonth(),
            'available_until' => now()->addMonth(),
        ];
    }

    public function current(): static
    {
        return $this->state(fn (array $attributes) => [
            'available_from' => now()->subWeek(),
            'available_until' => now()->addWeek(),
        ]);
    }

    public function upcoming(): static
    {
        return $this->state(fn (array $attributes) => [
            'available_from' => now()->addWeek(),
            'available_until' => now()->addMonth(),
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'available_from' => now()->subMonth(),
            'available_until' => now()->subWeek(),
        ]);
    }
}
