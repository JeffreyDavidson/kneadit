<?php

namespace Database\Factories\Platform;

use App\Models\Platform\FeatureUsageLog;
use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FeatureUsageLog> */
#[UseModel(FeatureUsageLog::class)]
class FeatureUsageLogFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => null,
            'feature' => fake()->word(),
            'usage_count' => fake()->numberBetween(1, 100),
            'last_used_at' => now(),
            'date' => now()->toDateString(),
        ];
    }

    /**
     * Log entry for a specific feature.
     */
    public function forFeature(string $feature): static
    {
        return $this->state(fn (array $attributes) => ['feature' => $feature]);
    }
}
