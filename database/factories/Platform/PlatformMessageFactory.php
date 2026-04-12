<?php

namespace Database\Factories\Platform;

use App\Enums\Platform\PlatformSenderType;
use App\Models\Platform\PlatformMessage;
use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PlatformMessage> */
#[UseModel(PlatformMessage::class)]
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
