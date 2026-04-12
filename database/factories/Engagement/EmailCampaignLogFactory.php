<?php

namespace Database\Factories\Engagement;

use App\Enums\Marketing\EmailDeliveryStatus;
use App\Models\Engagement\EmailCampaign;
use App\Models\Engagement\EmailCampaignLog;
use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EmailCampaignLog> */
#[UseModel(EmailCampaignLog::class)]
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
