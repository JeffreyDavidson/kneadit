<?php

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Customer;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subtotal = fake()->randomFloat(2, 10, 200);

        return [
            'order_number' => 'ORD-' . str_pad((string) fake()->unique()->randomNumber(6), 6, '0', STR_PAD_LEFT),
            'customer_id' => Customer::factory(),
            'user_id' => User::factory(),
            'status' => OrderStatus::Pending,
            'payment_status' => PaymentStatus::Unpaid,
            'subtotal' => $subtotal,
            'total' => $subtotal,
            'delivery_date' => fake()->optional()->dateTimeBetween('+1 day', '+30 days'),
        ];
    }

    /**
     * Order is confirmed.
     */
    public function confirmed(): static
    {
        return $this->state(fn (array $attributes) => ['status' => OrderStatus::Confirmed]);
    }

    /**
     * Order is being baked.
     */
    public function baking(): static
    {
        return $this->state(fn (array $attributes) => ['status' => OrderStatus::Baking]);
    }

    /**
     * Order is ready for pickup/delivery.
     */
    public function ready(): static
    {
        return $this->state(fn (array $attributes) => ['status' => OrderStatus::Ready]);
    }

    /**
     * Order has been delivered and paid.
     */
    public function delivered(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrderStatus::Delivered,
            'payment_status' => PaymentStatus::Paid,
        ]);
    }

    /**
     * Order has been cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => ['status' => OrderStatus::Cancelled]);
    }

    /**
     * Order has been paid.
     */
    public function paid(): static
    {
        return $this->state(fn (array $attributes) => ['payment_status' => PaymentStatus::Paid]);
    }
}
