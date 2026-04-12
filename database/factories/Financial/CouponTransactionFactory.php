<?php

namespace Database\Factories\Financial;

use App\Enums\Financial\CouponTransactionType;
use App\Models\Financial\Coupon;
use App\Models\Financial\CouponTransaction;
use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CouponTransaction>
 */
#[UseModel(CouponTransaction::class)]
class CouponTransactionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'coupon_id' => Coupon::factory(),
            'amount' => fake()->randomFloat(2, 1, 30),
            'type' => CouponTransactionType::Usage,
            'order_id' => null,
            'notes' => fake()->optional()->sentence(),
            'created_at' => now(),
        ];
    }

    public function usage(): static
    {
        return $this->state(fn (array $attributes) => ['type' => CouponTransactionType::Usage]);
    }

    public function reversal(): static
    {
        return $this->state(fn (array $attributes) => ['type' => CouponTransactionType::Reversal]);
    }
}
