<?php

namespace App\Filament\Pages\Analytics;

use App\Filament\Concerns\RequiresManagerRole;
use App\Models\Engagement\Survey;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * @property-read Survey|null $survey
 */
class SurveyResults extends Page
{
    use RequiresManagerRole;

    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    protected static string|\UnitEnum|null $navigationGroup = 'Communication';

    protected static ?int $navigationSort = 12;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $navigationLabel = 'Survey Results';

    protected static ?string $title = 'Survey Results';

    protected string $view = 'filament.pages.analytics.survey-results';

    public ?int $surveyId = null;

    public function getSurveyProperty(): ?Survey
    {
        return $this->surveyId ? Survey::with('responses')->find($this->surveyId) : null;
    }

    public function updatedSurveyId(): void
    {
        // Triggers reactivity
    }

    public function exportCsv(): ?StreamedResponse
    {
        $survey = $this->survey;
        if (! $survey) {
            return null;
        }

        return response()->streamDownload(static function () use ($survey) {
            $csvValue = static fn (mixed $value): bool|float|int|string|null => is_scalar($value) || $value === null
                ? $value
                : '';
            $handle = fopen('php://output', 'w');
            throw_if($handle === false, \RuntimeException::class, 'Failed to open file');
            $questions = $survey->questions;
            $headers = ['Response #', 'Customer Name', 'Customer Email', 'Date'];
            foreach ($questions as $q) {
                $headers[] = $csvValue($q['question']);
            }
            fputcsv($handle, $headers);

            foreach ($survey->responses as $i => $response) {
                $row = [
                    $i + 1,
                    $csvValue($response->customer_name),
                    $csvValue($response->customer_email),
                    $response->created_at?->format('Y-m-d H:i'),
                ];
                foreach ($questions as $qi => $q) {
                    $row[] = $csvValue($response->answers[$qi] ?? '');
                }
                fputcsv($handle, $row);
            }
            fclose($handle);
        }, "survey-{$survey->id}-results.csv");
    }

    protected function getViewData(): array
    {
        return [
            'surveys' => Survey::query()->orderBy('title')->pluck('title', 'id'),
            'survey' => $this->survey,
        ];
    }
}
