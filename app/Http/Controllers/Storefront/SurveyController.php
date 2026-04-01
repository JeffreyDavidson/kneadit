<?php

namespace App\Http\Controllers\Storefront;

use App\Actions\Customers\SubmitSurveyResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\StoreSurveyResponseRequest;
use App\Models\Survey;
use App\Services\Settings\TenantSettings;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class SurveyController extends Controller
{
    public function store(StoreSurveyResponseRequest $request, Survey $survey, SubmitSurveyResponse $submitResponse): RedirectResponse
    {
        $submitResponse(
            survey: $survey,
            answers: $request->validated('answers'),
            customerName: $request->validated('customer_name'),
            customerEmail: $request->validated('customer_email'),
        );

        return to_route('storefront.survey', $survey)->with('survey_submitted', true);
    }

    public function show(Survey $survey, TenantSettings $settings): View
    {
        $content = settingsPageContent('survey');

        return view('survey', [
            'settings' => $settings,
            'survey' => $survey,
            'content' => $content,
        ]);
    }
}
