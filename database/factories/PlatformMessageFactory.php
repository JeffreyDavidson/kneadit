<?php

namespace Database\Factories;

use App\Enums\PlatformSenderType;
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
            'sender_type' => PlatformSenderType::Admin,
            'subject' => fake()->sentence(),
            'body' => fake()->paragraph(),
            'parent_id' => null,
            'is_read' => false,
        ];
    }

    public function fromAdmin(): static
    {
        return $this->state(fn (array $attributes) => [
            'sender_type' => PlatformSenderType::Admin,
        ]);
    }

    public function fromTenant(): static
    {
        return $this->state(fn (array $attributes) => [
            'sender_type' => PlatformSenderType::Tenant,
        ]);
    }
}
