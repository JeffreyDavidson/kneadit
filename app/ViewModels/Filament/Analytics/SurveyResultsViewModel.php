<?php

namespace App\ViewModels\Filament\Analytics;

use App\Models\Engagement\Survey;
use App\Models\Engagement\SurveyResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

final readonly class SurveyResultsViewModel
{
    public int $responseCount;

    /**
     * @var array<int, array{
     *     question: string,
     *     type: string,
     *     average: float,
     *     answerCount: int,
     *     ratingDistribution: array<int, array{rating: int, count: int, percentage: float}>,
     *     choiceBreakdown: array<int, array{choice: int|string, count: int, percentage: int}>,
     *     textAnswers: array<int, mixed>
     * }>
     */
    public array $questionResults;

    /**
     * @param  Collection<int, SurveyResponse>  $responses
     */
    public function __construct(?Survey $survey, Collection $responses)
    {
        $this->responseCount = $responses->count();
        $questionResults = [];

        foreach ($survey === null ? [] : $survey->questions as $index => $question) {
            $answers = $responses->map(
                fn (SurveyResponse $response): mixed => Arr::get($response->answers, $index),
            );
            $type = Arr::string($question, 'type');

            $questionResults[$index] = match ($type) {
                'rating' => $this->ratingResult($question, $answers),
                'multiple_choice' => $this->choiceResult($question, $answers),
                'text' => $this->textResult($question, $answers),
                default => $this->emptyResult($question),
            };
        }

        $this->questionResults = $questionResults;
    }

    /**
     * @param  array<string, mixed>  $question
     * @param  Collection<int, mixed>  $answers
     * @return array{
     *     question: string,
     *     type: string,
     *     average: float,
     *     answerCount: int,
     *     ratingDistribution: array<int, array{rating: int, count: int, percentage: float}>,
     *     choiceBreakdown: array<int, array{choice: int|string, count: int, percentage: int}>,
     *     textAnswers: array<int, mixed>
     * }
     */
    private function ratingResult(array $question, Collection $answers): array
    {
        $ratings = $answers->filter()->map(static fn (mixed $value): int => is_numeric($value) ? (int) $value : 0);
        $counts = array_count_values($ratings->all());
        $maxCount = max(max($counts ?: [0]), 1);
        $distribution = [];

        foreach ([5, 4, 3, 2, 1] as $rating) {
            $count = $counts[$rating] ?? 0;
            $distribution[] = [
                'rating' => $rating,
                'count' => $count,
                'percentage' => ($count / $maxCount) * 100,
            ];
        }

        return [
            ...$this->emptyResult($question),
            'average' => $ratings->isEmpty() ? 0.0 : round((float) $ratings->avg(), 1),
            'answerCount' => $ratings->count(),
            'ratingDistribution' => $distribution,
        ];
    }

    /**
     * @param  array<string, mixed>  $question
     * @param  Collection<int, mixed>  $answers
     * @return array{
     *     question: string,
     *     type: string,
     *     average: float,
     *     answerCount: int,
     *     ratingDistribution: array<int, array{rating: int, count: int, percentage: float}>,
     *     choiceBreakdown: array<int, array{choice: int|string, count: int, percentage: int}>,
     *     textAnswers: array<int, mixed>
     * }
     */
    private function choiceResult(array $question, Collection $answers): array
    {
        $choiceCounts = [];

        foreach ($answers->filter() as $choice) {
            if (! is_int($choice) && ! is_string($choice)) {
                continue;
            }

            $choiceCounts[$choice] = ($choiceCounts[$choice] ?? 0) + 1;
        }

        arsort($choiceCounts);
        $count = array_sum($choiceCounts);
        $breakdown = [];

        foreach ($choiceCounts as $choice => $choiceCount) {
            $breakdown[] = [
                'choice' => $choice,
                'count' => $choiceCount,
                'percentage' => (int) round(($choiceCount / max($count, 1)) * 100),
            ];
        }

        return [
            ...$this->emptyResult($question),
            'answerCount' => $count,
            'choiceBreakdown' => $breakdown,
        ];
    }

    /**
     * @param  array<string, mixed>  $question
     * @param  Collection<int, mixed>  $answers
     * @return array{
     *     question: string,
     *     type: string,
     *     average: float,
     *     answerCount: int,
     *     ratingDistribution: array<int, array{rating: int, count: int, percentage: float}>,
     *     choiceBreakdown: array<int, array{choice: int|string, count: int, percentage: int}>,
     *     textAnswers: array<int, mixed>
     * }
     */
    private function textResult(array $question, Collection $answers): array
    {
        $texts = $answers->filter();

        return [
            ...$this->emptyResult($question),
            'answerCount' => $texts->count(),
            'textAnswers' => $texts->all(),
        ];
    }

    /**
     * @param  array<string, mixed>  $question
     * @return array{
     *     question: string,
     *     type: string,
     *     average: float,
     *     answerCount: int,
     *     ratingDistribution: array<int, array{rating: int, count: int, percentage: float}>,
     *     choiceBreakdown: array<int, array{choice: int|string, count: int, percentage: int}>,
     *     textAnswers: array<int, mixed>
     * }
     */
    private function emptyResult(array $question): array
    {
        return [
            'question' => Arr::string($question, 'question'),
            'type' => Arr::string($question, 'type'),
            'average' => 0.0,
            'answerCount' => 0,
            'ratingDistribution' => [],
            'choiceBreakdown' => [],
            'textAnswers' => [],
        ];
    }
}
