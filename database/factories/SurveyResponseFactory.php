<?php

namespace Database\Factories;

use App\Models\Survey;
use App\Models\SurveyResponse;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SurveyResponse>
 */
class SurveyResponseFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'survey_id' => Survey::factory(),
            'customer_name' => fake()->name(),
            'customer_email' => fake()->safeEmail(),
            'answers' => ['rating' => fake()->numberBetween(1, 5), 'feedback' => fake()->sentence()],
            'order_id' => null,
        ];
    }
}
