<?php

namespace Database\Factories\Financial;

use App\Enums\Financial\CouponType;
use App\Models\Financial\Coupon;
use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Coupon>
 */
#[UseModel(Coupon::class)]
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
            'percentage' => fake()->randomFloat(2, 5, 50),
            'fixed_amount' => null,
            'min_order_amount' => null,
            'max_uses' => null,
            'used_count' => 0,
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addMonth(),
            'is_active' => true,
        ];
    }

    /**
     * Coupon is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => ['is_active' => true]);
    }

    /**
     * Coupon is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => ['is_active' => false]);
    }

    /**
     * Coupon has reached its usage limit.
     */
    public function maxedOut(int $uses = 1): static
    {
        return $this->state(fn (array $attributes) => [
            'max_uses' => $uses,
            'used_count' => $uses,
        ]);
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
        return $this->state(fn (array $attributes) => [
            'type' => CouponType::Percentage,
            'percentage' => $attributes['percentage'] ?? fake()->randomFloat(2, 5, 50),
            'fixed_amount' => null,
        ]);
    }

    /**
     * Fixed-amount discount.
     */
    public function fixed(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => CouponType::Fixed,
            'fixed_amount' => $attributes['fixed_amount'] ?? fake()->randomFloat(2, 5, 25),
            'percentage' => null,
        ]);
    }
}
