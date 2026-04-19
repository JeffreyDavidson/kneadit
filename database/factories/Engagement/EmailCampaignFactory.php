<?php

namespace Database\Factories\Engagement;

use App\Enums\Marketing\EmailCampaignSegment;
use App\Enums\Marketing\EmailCampaignStatus;
use App\Models\Engagement\EmailCampaign;
use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EmailCampaign>
 */
#[UseModel(EmailCampaign::class)]
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
            'target_segment' => EmailCampaignSegment::All,
            'status' => EmailCampaignStatus::Draft,
            'recipient_count' => 0,
        ];
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => EmailCampaignStatus::Draft,
        ]);
    }

    public function scheduled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => EmailCampaignStatus::Scheduled,
            'scheduled_at' => now()->addDay(),
        ]);
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
