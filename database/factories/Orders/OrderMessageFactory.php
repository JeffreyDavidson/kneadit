<?php

namespace Database\Factories\Orders;

use App\Enums\Orders\SenderType;
use App\Models\Orders\Order;
use App\Models\Orders\OrderMessage;
use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderMessage>
 */
#[UseModel(OrderMessage::class)]
class OrderMessageFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'sender_type' => SenderType::Customer,
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
        return $this->state(fn (array $attributes) => [
            'sender_type' => SenderType::Baker,
        ]);
    }

    /**
     * Message sent by customer.
     */
    public function fromCustomer(): static
    {
        return $this->state(fn (array $attributes) => [
            'sender_type' => SenderType::Customer,
        ]);
    }
}
