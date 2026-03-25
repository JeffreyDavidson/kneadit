<?php

namespace Database\Factories;

use App\Models\SupportReply;
use App\Models\SupportTicket;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SupportReply> */
class SupportReplyFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ticket_id' => SupportTicket::factory(),
            'author_type' => 'admin',
            'author_name' => fake()->name(),
            'body' => fake()->paragraph(),
        ];
    }
}
