<?php

namespace Database\Factories\Orders;

use App\Enums\Orders\DeliveryType;
use App\Enums\Orders\OrderStatus;
use App\Enums\Orders\PaymentStatus;
use App\Models\Customers\Customer;
use App\Models\Orders\Order;
use App\Models\Orders\OrderItem;
use App\Models\Orders\OrderMessage;
use App\Models\Staff\User;
use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<Order>
 */
#[UseModel(Order::class)]
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
     * Order is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => ['status' => OrderStatus::Pending]);
    }

    /**
     * Order is unpaid.
     */
    public function unpaid(): static
    {
        return $this->state(fn (array $attributes) => ['payment_status' => PaymentStatus::Unpaid]);
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

    /**
     * Order is for delivery — sets delivery_type, fake address, and a
     * realistic delivery_date / delivery_time within business hours.
     * All four travel together so callers can't produce an incomplete
     * delivery order.
     */
    public function delivery(): static
    {
        return $this->state(fn (array $attributes) => [
            'delivery_type' => DeliveryType::Delivery,
            'delivery_address' => fake()->address(),
            'delivery_date' => fake()->dateTimeBetween('+1 day', '+30 days'),
            'delivery_time' => $this->randomBusinessTime(),
        ]);
    }

    /**
     * Order is for pickup — sets delivery_type, nulls the address, and
     * still sets a realistic delivery_date / delivery_time so every
     * pickup has a scheduled window (per the project rule that every
     * order must have a time).
     */
    public function pickup(): static
    {
        return $this->state(fn (array $attributes) => [
            'delivery_type' => DeliveryType::Pickup,
            'delivery_address' => null,
            'delivery_date' => fake()->dateTimeBetween('+1 day', '+30 days'),
            'delivery_time' => $this->randomBusinessTime(),
        ]);
    }

    /**
     * Random business-hour HH:MM string (8 AM – 5 PM, 15-min granularity).
     * Mirrors the helper in OrderSeeder so factory + seeder data agree.
     */
    private function randomBusinessTime(): string
    {
        $hours = [8, 9, 10, 11, 12, 13, 14, 15, 16, 17];
        $minutes = [0, 15, 30, 45];

        return sprintf('%02d:%02d', Arr::random($hours), Arr::random($minutes));
    }

    /**
     * Order has a specific delivery date.
     */
    public function withDeliveryDate(Carbon $date): static
    {
        return $this->state(fn (array $attributes) => ['delivery_date' => $date]);
    }

    /**
     * Order has line items.
     */
    public function withItems(int $count = 3): static
    {
        return $this->has(OrderItem::factory()->count($count), 'orderItems');
    }

    /**
     * Order has messages.
     */
    public function withMessages(int $count = 2): static
    {
        return $this->has(OrderMessage::factory()->count($count), 'messages');
    }
}
