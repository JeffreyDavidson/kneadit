<?php

namespace Database\Factories\Platform;

use App\Models\Platform\Setting;
use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Setting> */
#[UseModel(Setting::class)]
class SettingFactory extends Factory
{
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
