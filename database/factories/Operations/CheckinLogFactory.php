<?php

namespace Database\Factories\Operations;

use App\Models\Operations\CheckinLog;
use App\Models\Operations\ScheduledCheckin;
use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CheckinLog> */
#[UseModel(CheckinLog::class)]
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
