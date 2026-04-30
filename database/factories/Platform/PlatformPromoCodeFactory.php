<?php

namespace Database\Factories\Platform;

use App\Models\Platform\PlatformPromoCode;
use App\Models\Staff\User;
use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PlatformPromoCode>
 */
#[UseModel(PlatformPromoCode::class)]
class PlatformPromoCodeFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => strtoupper(fake()->bothify('PROMO-####')),
            'coupon_id' => 'coupon_' . fake()->unique()->bothify('????????'),
            'promotion_code_id' => 'promo_' . fake()->unique()->bothify('????????'),
            'percent_off' => fake()->numberBetween(5, 50),
            'amount_off_cents' => null,
            'duration' => fake()->randomElement(['once', 'repeating', 'forever']),
            'duration_in_months' => null,
            'max_redemptions' => 1,
            'expires_at' => null,
            'tenant_id' => null,
            'name' => fake()->sentence(3),
            'created_by_user_id' => User::factory(),
        ];
    }

    public function amountOff(int $cents = 500): static
    {
        return $this->state(fn (array $attributes) => [
            'percent_off' => null,
            'amount_off_cents' => $cents,
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => ['expires_at' => now()->subDay()]);
    }
}
