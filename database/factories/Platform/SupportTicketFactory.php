<?php

namespace Database\Factories\Platform;

use App\Enums\Platform\SupportTicketPriority;
use App\Enums\Platform\SupportTicketStatus;
use App\Models\Platform\SupportReply;
use App\Models\Platform\SupportTicket;
use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SupportTicket> */
#[UseModel(SupportTicket::class)]
class SupportTicketFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => null,
            'subject' => fake()->sentence(),
            'body' => fake()->paragraph(),
            'status' => SupportTicketStatus::Open,
            'priority' => SupportTicketPriority::Normal,
        ];
    }

    public function open(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => SupportTicketStatus::Open,
        ]);
    }

    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => SupportTicketStatus::InProgress,
        ]);
    }

    public function resolved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => SupportTicketStatus::Resolved,
        ]);
    }

    public function closed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => SupportTicketStatus::Closed,
        ]);
    }

    public function highPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => SupportTicketPriority::High,
        ]);
    }

    /**
     * Ticket has replies.
     */
    public function withReplies(int $count = 2): static
    {
        return $this->has(SupportReply::factory()->count($count), 'replies');
    }
}
