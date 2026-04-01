<?php

namespace Database\Factories\Platform;

use App\Enums\Platform\AnnouncementType;
use App\Models\Platform\PlatformAnnouncement;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PlatformAnnouncement>
 */
class PlatformAnnouncementFactory extends Factory
{
    protected $model = PlatformAnnouncement::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(),
            'body' => fake()->paragraph(),
            'type' => fake()->randomElement(AnnouncementType::cases()),
            'target_plans' => null,
            'is_active' => true,
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => ['is_active' => true]);
    }

    /**
     * Announcement is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => ['is_active' => false]);
    }
}
