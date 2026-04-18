<?php

namespace Database\Factories\Customers;

use App\Models\Customers\Customer;
use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Customer>
 */
#[UseModel(Customer::class)]
class CustomerFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->optional()->passthrough(fake()->numerify('###-###-####')),
            'birthday' => null,
        ];
    }

    /**
     * Customer has a birthday set.
     */
    public function withBirthday(): static
    {
        return $this->state(fn (array $attributes) => ['birthday' => fake()->date()]);
    }

    /**
     * Customer has a login password set.
     */
    public function withPassword(string $password = 'password123'): static
    {
        return $this->state(fn (array $attributes) => ['password' => $password]);
    }

    /**
     * Customer has a full address.
     */
    public function withAddress(): static
    {
        return $this->state(fn (array $attributes) => [
            'address' => fake()->streetAddress(),
            'city' => fake()->city(),
            'state' => fake()->stateAbbr(),
            'zip' => fake()->postcode(),
        ]);
    }
}
