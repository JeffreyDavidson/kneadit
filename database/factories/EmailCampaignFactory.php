<?php

namespace Database\Factories;

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
            'status' => 'draft',
            'recipient_count' => 0,
        ];
    }
}
