<?php

namespace Database\Factories;

use App\Enums\SupportReplyAuthorType;
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
            'author_type' => fake()->randomElement(SupportReplyAuthorType::cases()),
            'author_name' => fake()->name(),
            'body' => fake()->paragraph(),
        ];
    }
}
