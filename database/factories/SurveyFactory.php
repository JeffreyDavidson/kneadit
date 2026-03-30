<?php

namespace Database\Factories;

use App\Models\Survey;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Survey>
 */
class SurveyFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(4),
            'description' => fake()->optional()->paragraph(),
            'questions' => [
                ['question' => 'How was your experience?', 'type' => 'rating'],
                ['question' => 'Any feedback?', 'type' => 'text'],
            ],
            'is_active' => true,
            'responses_count' => 0,
        ];
    }

    /**
     * Survey is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => ['is_active' => false]);
    }
}
