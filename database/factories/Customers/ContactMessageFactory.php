<?php

namespace Database\Factories\Customers;

use App\Models\Customers\ContactMessage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ContactMessage> */
class ContactMessageFactory extends Factory
{
    protected $model = ContactMessage::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'subject' => fake()->sentence(),
            'message' => fake()->paragraph(),
            'is_read' => false,
        ];
    }

    public function unread(): static
    {
        return $this->state(fn (array $attributes) => ['is_read' => false]);
    }

    public function read(): static
    {
        return $this->state(fn (array $attributes) => ['is_read' => true]);
    }
}
