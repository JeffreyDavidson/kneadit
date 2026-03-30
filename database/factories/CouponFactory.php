<?php

namespace Database\Factories;

use App\Enums\CouponType;
use App\Models\Coupon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Coupon>
 */
class CouponFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => strtoupper(fake()->unique()->bothify('??##??')),
            'type' => CouponType::Percentage,
            'value' => fake()->randomFloat(2, 5, 50),
            'min_order_amount' => null,
            'max_uses' => null,
            'used_count' => 0,
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addMonth(),
            'is_active' => true,
        ];
    }

    /**
     * Coupon is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => ['is_active' => false]);
    }

    /**
     * Coupon has expired.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => ['expires_at' => now()->subDay()]);
    }

    /**
     * Percentage-based discount.
     */
    public function percentage(): static
    {
        return $this->state(fn (array $attributes) => ['type' => CouponType::Percentage]);
    }

    /**
     * Fixed-amount discount.
     */
    public function fixed(): static
    {
        return $this->state(fn (array $attributes) => ['type' => CouponType::Fixed]);
    }
}
