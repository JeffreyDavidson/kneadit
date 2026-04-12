<?php

namespace Database\Factories\Engagement;

use App\Models\Engagement\Survey;
use App\Models\Engagement\SurveyResponse;
use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SurveyResponse>
 */
#[UseModel(SurveyResponse::class)]
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
