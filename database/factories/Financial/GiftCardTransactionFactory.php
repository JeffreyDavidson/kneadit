<?php

namespace Database\Factories\Financial;

use App\Enums\Financial\GiftCardTransactionType;
use App\Models\Financial\GiftCard;
use App\Models\Financial\GiftCardTransaction;
use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GiftCardTransaction>
 */
#[UseModel(GiftCardTransaction::class)]
class GiftCardTransactionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'gift_card_id' => GiftCard::factory(),
            'amount' => fake()->randomFloat(2, 5, 50),
            'type' => GiftCardTransactionType::Purchase,
            'order_id' => null,
            'notes' => fake()->optional()->sentence(),
            'created_at' => now(),
        ];
    }

    /**
     * Transaction is a redemption.
     */
    public function redemption(): static
    {
        return $this->state(fn (array $attributes) => ['type' => GiftCardTransactionType::Redemption]);
    }

    /**
     * Transaction is a credit/refund.
     */
    public function credit(): static
    {
        return $this->state(fn (array $attributes) => ['type' => GiftCardTransactionType::Refund]);
    }
}
