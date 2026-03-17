<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Survey;
use App\Models\SurveyResponse;
use Illuminate\Http\Request;

class SurveyController extends Controller
{
    public function show(Survey $survey)
    {
        abort_unless($survey->is_active, 404);

        return view('survey', compact('survey'));
    }

    public function store(Request $request, Survey $survey)
    {
        abort_unless($survey->is_active, 404);

        $request->validate([
            'customer_name' => ['nullable', 'string', 'max:255'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'answers' => ['required', 'array'],
        ]);

        $sanitizedAnswers = array_map(
            fn ($answer) => is_string($answer) ? strip_tags($answer) : $answer,
            array_values($request->answers)
        );

        SurveyResponse::create([
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
