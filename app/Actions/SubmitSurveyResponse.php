<?php

namespace App\Actions;

use App\Models\Survey;
use App\Models\SurveyResponse;

class SubmitSurveyResponse
{
    /**
     * @param  array<int, mixed>  $answers
     */
    public function __invoke(Survey $survey, array $answers, ?string $customerName = null, ?string $customerEmail = null): SurveyResponse
    {
        $sanitizedAnswers = array_map(
            fn (mixed $answer) => is_string($answer) ? strip_tags($answer) : $answer,
            array_values($answers)
        );

        $response = SurveyResponse::query()->create([
            'survey_id' => $survey->id,
            'customer_name' => $customerName,
            'customer_email' => $customerEmail,
            'answers' => $sanitizedAnswers,
            'created_at' => now(),
        ]);

        $survey->increment('responses_count');

        return $response;
    }
}
