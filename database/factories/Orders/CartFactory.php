<?php

namespace Database\Factories\Orders;

use App\Models\Orders\Cart;
use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Cart>
 */
#[UseModel(Cart::class)]
class CartFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'cart_token' => (string) Str::ulid(),
            'customer_email' => null,
            'customer_name' => null,
            'last_activity_at' => now(),
            'expires_at' => now()->addDays(30),
            'recovery_sent_at' => null,
            'converted_at' => null,
        ];
    }

    public function withEmail(string $email = 'customer@example.com'): static
    {
        return $this->state(fn (array $attributes) => ['customer_email' => $email]);
    }

    public function abandoned(int $hoursAgo = 24): static
    {
        return $this->state(fn (array $attributes) => [
            'last_activity_at' => now()->subHours($hoursAgo),
        ]);
    }

    public function converted(): static
    {
        return $this->state(fn (array $attributes) => [
            'converted_at' => now(),
        ]);
    }
}
