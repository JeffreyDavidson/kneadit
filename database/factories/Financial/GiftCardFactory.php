<?php

namespace Database\Factories\Financial;

use App\Models\Financial\GiftCard;
use App\Models\Financial\GiftCardTransaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GiftCard>
 */
class GiftCardFactory extends Factory
{
    protected $model = GiftCard::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $balance = fake()->randomFloat(2, 10, 100);

        return [
            'code' => strtoupper(fake()->unique()->bothify('????-????-????-????')),
            'initial_balance' => $balance,
            'current_balance' => $balance,
            'purchaser_name' => fake()->name(),
            'purchaser_email' => fake()->safeEmail(),
            'recipient_name' => fake()->optional()->name(),
            'recipient_email' => fake()->optional()->safeEmail(),
            'message' => fake()->optional()->sentence(),
            'is_active' => true,
            'expires_at' => now()->addYear(),
        ];
    }

    /**
     * Gift card balance is zero.
     */
    public function depleted(): static
    {
        return $this->state(fn (array $attributes) => ['current_balance' => 0]);
    }

    /**
     * Gift card is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => ['is_active' => false]);
    }

    /**
     * Gift card has expired.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => ['expires_at' => now()->subDay()]);
    }

    /**
     * Gift card has transaction history.
     */
    public function withTransactions(int $count = 3): static
    {
        return $this->has(GiftCardTransaction::factory()->count($count), 'transactions');
    }
}
