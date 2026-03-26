<?php

namespace App\Http\Controllers\Storefront;

use App\Actions\Customers\SubmitSurveyResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSurveyResponseRequest;
use App\Models\Survey;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class SurveyController extends Controller
{
    public function show(Survey $survey): View
    {
        abort_unless($survey->is_active, 404);

        return view('survey', compact('survey'));
    }

    public function store(StoreSurveyResponseRequest $request, Survey $survey, SubmitSurveyResponse $submitResponse): RedirectResponse
    {
        abort_unless($survey->is_active, 404);

        $validated = $request->validated();

        $submitResponse(
            survey: $survey,
            answers: (array) $validated['answers'],
            customerName: isset($validated['customer_name']) ? (string) $validated['customer_name'] : null,
            customerEmail: isset($validated['customer_email']) ? (string) $validated['customer_email'] : null,
        );

        return to_route('storefront.survey', $survey)->with('survey_submitted', true);
    }
}
