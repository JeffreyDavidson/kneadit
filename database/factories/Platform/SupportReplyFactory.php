<?php

namespace Database\Factories\Platform;

use App\Enums\Platform\SupportReplyAuthorType;
use App\Models\Platform\SupportReply;
use App\Models\Platform\SupportTicket;
use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SupportReply> */
#[UseModel(SupportReply::class)]
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
