<?php

namespace Database\Factories\Platform;

use App\Models\Platform\PlatformActivity;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PlatformActivity> */
class PlatformActivityFactory extends Factory
{
    protected $model = PlatformActivity::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event' => fake()->randomElement(['tenant.created', 'tenant.updated', 'subscription.changed']),
            'tenant_id' => null,
            'description' => fake()->sentence(),
            'metadata' => null,
        ];
    }
}
