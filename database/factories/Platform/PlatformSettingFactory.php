<?php

namespace Database\Factories\Platform;

use App\Models\Platform\PlatformSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PlatformSetting> */
class PlatformSettingFactory extends Factory
{
    protected $model = PlatformSetting::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'key' => fake()->unique()->slug(2),
            'value' => fake()->word(),
        ];
    }
}
