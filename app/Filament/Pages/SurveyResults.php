<?php

namespace App\Filament\Pages;

use App\Models\Survey;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SurveyResults extends Page implements HasForms
{
    use InteractsWithForms;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-chat-bubble-left-right';
    protected static string | \UnitEnum | null $navigationGroup = 'Communication';
    protected static ?int $navigationSort = 12;
    protected static ?string $navigationLabel = 'Survey Results';
    protected static ?string $title = 'Survey Results';
    protected string $view = 'filament.pages.survey-results';

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
        if (!$survey) return null;

        return response()->streamDownload(function () use ($survey) {
            $handle = fopen('php://output', 'w');
            $questions = $survey->questions;
            $headers = ['Response #', 'Customer Name', 'Customer Email', 'Date'];
            foreach ($questions as $q) {
                $headers[] = $q['question'];
            }
            fputcsv($handle, $headers);

            foreach ($survey->responses as $i => $response) {
                $row = [$i + 1, $response->customer_name, $response->customer_email, $response->created_at?->format('Y-m-d H:i')];
                foreach ($questions as $qi => $q) {
                    $row[] = $response->answers[$qi] ?? '';
                }
                fputcsv($handle, $row);
            }
            fclose($handle);
        }, "survey-{$survey->id}-results.csv");
    }

    protected function getViewData(): array
    {
        return [
            'surveys' => Survey::orderBy('title')->pluck('title', 'id'),
            'survey' => $this->survey,
        ];
    }
}
