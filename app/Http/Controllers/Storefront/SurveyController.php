<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSurveyResponseRequest;
use App\Models\Survey;
use App\Models\SurveyResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class SurveyController extends Controller
{
    public function show(Survey $survey): View
    {
        abort_unless($survey->is_active, 404);

        return view('survey', compact('survey'));
    }

    public function store(StoreSurveyResponseRequest $request, Survey $survey): RedirectResponse
    {
        abort_unless($survey->is_active, 404);

        $sanitizedAnswers = array_map(
            fn (mixed $answer) => is_string($answer) ? strip_tags($answer) : $answer,
            array_values($request->answers)
        );

        SurveyResponse::query()->create([
            'survey_id' => $survey->id,
            'customer_name' => $request->customer_name ? strip_tags($request->customer_name) : null,
            'customer_email' => $request->customer_email,
            'answers' => $sanitizedAnswers,
            'created_at' => now(),
        ]);

        $survey->increment('responses_count');

        return to_route('storefront.survey', $survey)->with('survey_submitted', true);
    }
}
