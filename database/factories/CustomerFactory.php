<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Customer>
 */
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
            'phone' => fake()->optional()->phoneNumber(),
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
