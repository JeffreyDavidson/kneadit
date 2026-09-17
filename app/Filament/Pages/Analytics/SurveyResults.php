<?php

namespace App\Filament\Pages\Analytics;

use App\Filament\Concerns\RequiresManagerRole;
use App\Models\Engagement\Survey;
use App\Services\Export\CsvValueSanitizer;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Livewire\Attributes\Computed;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * @property-read Survey|null $survey
 */
class SurveyResults extends Page
{
    use RequiresManagerRole;

    #[\Override]
    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    #[\Override]
    protected static string|\UnitEnum|null $navigationGroup = 'Communication';

    #[\Override]
    protected static ?int $navigationSort = 12;

    #[\Override]
    protected static bool $shouldRegisterNavigation = false;

    #[\Override]
    protected static ?string $navigationLabel = 'Survey Results';

    #[\Override]
    protected static ?string $title = 'Survey Results';

    #[\Override]
    protected string $view = 'filament.pages.analytics.survey-results';

    public ?int $surveyId = null;

    #[Computed]
    public function survey(): ?Survey
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

        return response()->streamDownload(static function () use ($survey): void {
            $csvValue = CsvValueSanitizer::sanitize(...);
            $handle = fopen('php://output', 'w');
            throw_if($handle === false, \RuntimeException::class, 'Failed to open file');
            $questions = $survey->questions;
            $headers = ['Response #', 'Customer Name', 'Customer Email', 'Date'];
            foreach ($questions as $q) {
                $headers[] = $csvValue($q['question']);
            }
            fputcsv($handle, $headers, escape: '\\');

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
                fputcsv($handle, $row, escape: '\\');
            }
            fclose($handle);
        }, "survey-{$survey->id}-results.csv");
    }

    #[\Override]
    protected function getViewData(): array
    {
        return [
            'surveys' => Survey::query()->orderBy('title')->pluck('title', 'id'),
            'survey' => $this->survey,
        ];
    }
}
