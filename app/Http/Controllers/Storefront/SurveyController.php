<?php

namespace App\Http\Controllers\Storefront;

use App\Actions\Customers\SubmitSurveyResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\StoreSurveyResponseRequest;
use App\Models\Engagement\Survey;
use App\Services\Settings\TenantSettings;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class SurveyController extends Controller
{
    public function store(StoreSurveyResponseRequest $request, Survey $survey, SubmitSurveyResponse $submitResponse): RedirectResponse
    {
        $submitResponse(
            survey: $survey,
            answers: array_values($request->array('answers')),
            customerName: $request->filled('customer_name') ? $request->string('customer_name')->toString() : null,
            customerEmail: $request->filled('customer_email') ? $request->string('customer_email')->toString() : null,
        );

        return to_route('storefront.survey', $survey)->with('survey_submitted', true);
    }

    public function show(Survey $survey, TenantSettings $settings): View
    {
        $content = settingsPageContent('survey');

        return view('storefront.survey', [
            'settings' => $settings,
            'survey' => $survey,
            'content' => $content,
        ]);
    }
}
