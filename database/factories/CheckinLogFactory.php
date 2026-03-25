<?php

namespace Database\Factories;

use App\Models\CheckinLog;
use App\Models\ScheduledCheckin;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CheckinLog> */
class CheckinLogFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'checkin_id' => ScheduledCheckin::factory(),
            'tenant_id' => fake()->uuid(),
            'sent_at' => now(),
        ];
    }
}
