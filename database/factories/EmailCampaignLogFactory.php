<?php

namespace Database\Factories;

use App\Enums\EmailDeliveryStatus;
use App\Models\EmailCampaign;
use App\Models\EmailCampaignLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EmailCampaignLog> */
class EmailCampaignLogFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'campaign_id' => EmailCampaign::factory(),
            'tenant_id' => null,
            'email' => fake()->safeEmail(),
            'status' => EmailDeliveryStatus::Sent,
            'sent_at' => now(),
        ];
    }
}
