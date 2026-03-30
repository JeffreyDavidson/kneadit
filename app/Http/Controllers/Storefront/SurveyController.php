<?php

namespace App\Http\Controllers\Storefront;

use App\Actions\Customers\SubmitSurveyResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSurveyResponseRequest;
use App\Models\Survey;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;

class SurveyController extends Controller
{
    public function show(Survey $survey): View
    {
        abort_unless($survey->is_active, 404);

        $storeName = settings('store_name', 'Our Bakery');
        $heroImage = settings('hero_image');
        $heroImageUrl = $heroImage ? Storage::url($heroImage) : 'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=1920&q=80';
        $content = settingsPageContent('survey');

        return view('survey', [
            'survey' => $survey,
            'storeName' => $storeName,
            'heroImageUrl' => $heroImageUrl,
            'content' => $content,
        ]);
    }

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
}
