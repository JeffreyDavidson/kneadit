<?php

namespace Database\Factories;

use App\Enums\EmailCampaignStatus;
use App\Models\EmailCampaign;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EmailCampaign> */
class EmailCampaignFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'subject' => fake()->sentence(),
            'body' => fake()->paragraphs(3, true),
            'target_segment' => 'all',
            'status' => EmailCampaignStatus::Draft,
            'recipient_count' => 0,
        ];
    }

    public function sending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => EmailCampaignStatus::Sending,
        ]);
    }

    public function sent(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => EmailCampaignStatus::Sent,
        ]);
    }
}
