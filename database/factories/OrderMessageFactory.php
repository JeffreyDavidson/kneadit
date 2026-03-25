<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderMessage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderMessage>
 */
class OrderMessageFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'sender_type' => 'customer',
            'sender_name' => fake()->name(),
            'message' => fake()->paragraph(),
            'is_read' => false,
        ];
    }

    /**
     * Message sent by baker.
     */
    public function fromBaker(): static
    {
        return $this->state(fn (array $attributes) => ['sender_type' => 'baker']);
    }

    /**
     * Message sent by customer.
     */
    public function fromCustomer(): static
    {
        return $this->state(fn (array $attributes) => ['sender_type' => 'customer']);
    }
}
