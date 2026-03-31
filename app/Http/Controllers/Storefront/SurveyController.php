<?php

namespace App\Http\Controllers\Storefront;

use App\Actions\Customers\SubmitSurveyResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSurveyResponseRequest;
use App\Models\Survey;
use App\Services\Settings\TenantSettings;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class SurveyController extends Controller
{
    public function store(StoreSurveyResponseRequest $request, Survey $survey, SubmitSurveyResponse $submitResponse): RedirectResponse
    {
        abort_unless($survey->is_active, 404);

        $validated = $request->validated();

        $submitResponse(
            survey: $survey,
            answers: $validated['answers'],
            customerName: $validated['customer_name'] ?? null,
            customerEmail: $validated['customer_email'] ?? null,
        );

        return to_route('storefront.survey', $survey)->with('survey_submitted', true);
    }

    public function show(Survey $survey, TenantSettings $settings): View
    {
        abort_unless($survey->is_active, 404);

        $content = settingsPageContent('survey');

        return view('survey', [
            'settings' => $settings,
            'survey' => $survey,
            'content' => $content,
        ]);
    }
}
