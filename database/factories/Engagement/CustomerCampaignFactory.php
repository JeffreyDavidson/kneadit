<?php

namespace Database\Factories\Engagement;

use App\Enums\Marketing\CustomerCampaignStatus;
use App\Models\Engagement\CustomerCampaign;
use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CustomerCampaign>
 */
#[UseModel(CustomerCampaign::class)]
class CustomerCampaignFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'subject' => fake()->sentence(),
            'body' => fake()->paragraphs(2, true),
            'target_segment' => 'all',
            'status' => CustomerCampaignStatus::Draft,
            'sent_at' => null,
            'recipient_count' => 0,
        ];
    }

    public function sent(int $count = 25): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => CustomerCampaignStatus::Sent,
            'sent_at' => now(),
            'recipient_count' => $count,
        ]);
    }
}
