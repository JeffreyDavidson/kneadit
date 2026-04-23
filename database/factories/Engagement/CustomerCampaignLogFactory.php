<?php

namespace Database\Factories\Engagement;

use App\Models\Engagement\CustomerCampaign;
use App\Models\Engagement\CustomerCampaignLog;
use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<CustomerCampaignLog>
 */
#[UseModel(CustomerCampaignLog::class)]
class CustomerCampaignLogFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_campaign_id' => CustomerCampaign::factory(),
            'customer_email' => fake()->safeEmail(),
            'tracking_token' => (string) Str::ulid(),
            'opened_at' => null,
        ];
    }

    public function opened(): static
    {
        return $this->state(fn (array $attributes) => ['opened_at' => now()]);
    }
}
