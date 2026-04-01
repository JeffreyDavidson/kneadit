<?php

namespace Database\Factories\Operations;

use App\Models\Operations\CheckinLog;
use App\Models\Operations\ScheduledCheckin;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CheckinLog> */
class CheckinLogFactory extends Factory
{
    protected $model = CheckinLog::class;

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
