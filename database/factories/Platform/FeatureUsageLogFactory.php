<?php

namespace Database\Factories\Platform;

use App\Models\Platform\FeatureUsageLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FeatureUsageLog> */
class FeatureUsageLogFactory extends Factory
{
    protected $model = FeatureUsageLog::class;

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
}
