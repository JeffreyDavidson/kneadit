<?php

namespace Database\Factories\Financial;

use App\Models\Financial\Refund;
use App\Models\Orders\Order;
use App\ValueObjects\Money;
use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Refund>
 */
#[UseModel(Refund::class)]
class RefundFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'user_id' => null,
            'amount' => Money::fromDollars(fake()->randomFloat(2, 1, 100)),
            'reason' => fake()->sentence(),
            'stripe_refund_id' => 're_' . fake()->unique()->bothify('??##??##??##'),
        ];
    }
}
