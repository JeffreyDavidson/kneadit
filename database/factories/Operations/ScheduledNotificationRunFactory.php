<?php

namespace Database\Factories\Operations;

use App\Models\Operations\ScheduledNotificationRun;
use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ScheduledNotificationRun>
 */
#[UseModel(ScheduledNotificationRun::class)]
class ScheduledNotificationRunFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'notification_key' => fake()->unique()->slug(),
            'claimed_at' => now(),
        ];
    }
}
