<?php

namespace Database\Factories;

use App\Models\PlatformMessage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PlatformMessage> */
class PlatformMessageFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => null,
            'sender_type' => 'admin',
            'subject' => fake()->sentence(),
            'body' => fake()->paragraph(),
            'parent_id' => null,
            'is_read' => false,
        ];
    }
}
