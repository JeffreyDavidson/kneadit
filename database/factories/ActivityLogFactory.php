<?php

namespace Database\Factories;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ActivityLog> */
class ActivityLogFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => null,
            'user_name' => fake()->name(),
            'action' => fake()->randomElement(['created', 'updated', 'deleted']),
            'model_type' => 'App\\Models\\Order',
            'model_id' => fake()->numberBetween(1, 100),
            'changes' => null,
            'ip_address' => fake()->ipv4(),
            'created_at' => now(),
        ];
    }
}
