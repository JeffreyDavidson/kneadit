<?php

namespace Database\Factories\Operations;

use App\Models\Operations\WebhookDelivery;
use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WebhookDelivery>
 */
#[UseModel(WebhookDelivery::class)]
class WebhookDeliveryFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $dispatchedAt = fake()->dateTimeBetween('-30 days');

        return [
            'event' => fake()->randomElement(['order.created', 'order.updated', 'order.cancelled', 'order.delivered']),
            'url' => 'https://hooks.example.com/' . fake()->uuid(),
            'payload' => ['order_number' => 'ORD-' . fake()->numberBetween(1, 9999)],
            'signature' => hash_hmac('sha256', 'test', 'secret'),
            'status_code' => 200,
            'response_body' => 'ok',
            'attempt' => 1,
            'succeeded' => true,
            'error' => null,
            'dispatched_at' => $dispatchedAt,
            'responded_at' => (clone $dispatchedAt)->modify('+1 second'),
        ];
    }

    public function succeeded(): static
    {
        return $this->state(fn (array $attributes) => [
            'status_code' => 200,
            'response_body' => 'ok',
            'succeeded' => true,
            'error' => null,
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status_code' => 500,
            'response_body' => 'Internal Server Error',
            'succeeded' => false,
            'error' => 'Endpoint returned 500',
        ]);
    }
}
