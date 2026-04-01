<?php

namespace Database\Factories\Operations;

use App\Models\Operations\ActivityLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ActivityLog> */
class ActivityLogFactory extends Factory
{
    protected $model = ActivityLog::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => null,
            'user_name' => fake()->name(),
            'action' => fake()->randomElement(['created', 'updated', 'deleted']),
            'description' => fake()->sentence(),
            'model_type' => 'App\\Models\\Orders\\Order',
            'model_id' => fake()->numberBetween(1, 100),
            'properties' => null,
            'ip_address' => fake()->ipv4(),
            'created_at' => now(),
        ];
    }
}
